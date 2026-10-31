<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RajaOngkirController extends Controller
{
    /**
     * Kurir yang ditampilkan pada halaman uji (RajaOngkir V2 mendukung lebih banyak).
     */
    private const COURIERS = [
        'jne' => 'JNE',
        'tiki' => 'TIKI',
        'pos' => 'POS Indonesia',
    ];

    /**
     * Daftar provinsi.
     */
    public function getProvinces(): JsonResponse
    {
        return $this->fetchLocations(
            'v1/destination/province',
            fn (array $item) => [
                'province_id' => $item['id'] ?? $item['province_id'] ?? null,
                'province' => $item['name'] ?? $item['province'] ?? null,
            ]
        );
    }

    /**
     * Daftar kota/kabupaten berdasarkan provinsi.
     */
    public function getCities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'min:1'],
        ]);

        return $this->fetchLocations(
            'v1/destination/city/' . $validated['province_id'],
            fn (array $item) => [
                'city_id' => $item['id'] ?? $item['city_id'] ?? null,
                'city_name' => $item['name'] ?? $item['city_name'] ?? null,
            ]
        );
    }

    /**
     * Daftar kecamatan berdasarkan kota/kabupaten.
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'integer', 'min:1'],
        ]);

        return $this->fetchLocations(
            'v1/destination/district/' . $validated['city_id'],
            fn (array $item) => [
                'district_id' => $item['id'] ?? $item['district_id'] ?? null,
                'district_name' => $item['name'] ?? $item['district_name'] ?? null,
            ]
        );
    }

    /**
     * Menghitung ongkos kirim memakai ID kecamatan asal dan tujuan.
     */
    public function getCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin' => ['required', 'integer', 'min:1'],
            'destination' => ['required', 'integer', 'min:1'],
            'weight' => ['required', 'integer', 'min:1'],
            'courier' => ['required', Rule::in(array_keys(self::COURIERS))],
        ]);

        return $this->requestRajaOngkir(
            fn () => $this->client()->asForm()->post('v1/calculate/domestic-cost', $validated),
            function (array $payload) {
                $rates = $payload['data'] ?? [];

                if (! is_array($rates)) {
                    return [];
                }

                // Sebagian respons mengembalikan satu objek, bukan array of objects.
                if (isset($rates['service'])) {
                    $rates = [$rates];
                }

                return array_values(array_map(function (array $rate) {
                    $code = strtolower((string) ($rate['code'] ?? ''));

                    return [
                        'code' => $code,
                        'name' => self::COURIERS[$code] ?? strtoupper($code),
                        'service' => $rate['service'] ?? '-',
                        'description' => $rate['description'] ?? '',
                        'cost' => (int) ($rate['cost'] ?? 0),
                        'currency' => $rate['currency'] ?? 'IDR',
                        'etd' => trim((string) ($rate['etd'] ?? '')),
                        'note' => $rate['note'] ?? '',
                    ];
                }, array_filter($rates, 'is_array')));
            }
        );
    }

    /**
     * HTTP client RajaOngkir V2; API key dibaca dari config agar aman dan
     * tetap bekerja saat `php artisan config:cache`.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/') . '/')
            ->withHeaders(['key' => (string) config('services.rajaongkir.api_key')])
            ->acceptJson()
            ->timeout((int) config('services.rajaongkir.timeout', 15));
    }

    /**
     * Mengambil data lokasi (provinsi/kota/kecamatan) dan menormalkannya
     * agar bentuk responsnya konsisten untuk frontend.
     */
    private function fetchLocations(string $endpoint, Closure $normalizer): JsonResponse
    {
        return $this->requestRajaOngkir(
            fn () => $this->client()->get($endpoint),
            function (array $payload) use ($normalizer) {
                $items = $payload['data'] ?? [];

                if (! is_array($items)) {
                    return [];
                }

                return array_values(array_map(
                    $normalizer,
                    array_filter($items, 'is_array')
                ));
            }
        );
    }

    /**
     * Eksekusi request + validasi respons V2 (`meta.code` dan `data`).
     */
    private function requestRajaOngkir(Closure $request, Closure $normalizer): JsonResponse
    {
        if (blank(config('services.rajaongkir.api_key'))) {
            return $this->errorResponse(
                'RAJAONGKIR_API_KEY belum diatur. Isi API key di file .env terlebih dahulu.',
                503
            );
        }

        try {
            $response = $request();
        } catch (ConnectionException $exception) {
            Log::warning('Gagal terhubung ke RajaOngkir: ' . $exception->getMessage());

            return $this->errorResponse(
                'Tidak dapat terhubung ke API RajaOngkir. Periksa koneksi internet.',
                502
            );
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['meta']['code'])) {
            Log::warning('Respons RajaOngkir tidak dikenali.', [
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->errorResponse('Respons API RajaOngkir tidak dikenali.', 502);
        }

        if ((int) $payload['meta']['code'] !== 200) {
            $message = $payload['meta']['message'] ?? 'Permintaan ke RajaOngkir gagal.';
            $status = $response->successful() ? 502 : max($response->status(), 400);

            return $this->errorResponse($message, $status);
        }

        return response()->json([
            'rajaongkir' => [
                'status' => [
                    'code' => 200,
                    'description' => 'OK',
                ],
                'results' => $normalizer($payload),
            ],
        ]);
    }

    /**
     * Format error yang konsisten untuk frontend.
     */
    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'rajaongkir' => [
                'status' => [
                    'code' => $status,
                    'description' => $message,
                ],
                'results' => [],
            ],
        ], $status);
    }
}
