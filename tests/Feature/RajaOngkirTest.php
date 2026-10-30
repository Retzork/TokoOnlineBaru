<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.api_key' => 'test-key',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api',
            'services.rajaongkir.timeout' => 5,
        ]);

        Http::preventStrayRequests();
    }

    public function test_halaman_cek_ongkir_dapat_dibuka(): void
    {
        $this->get('/cek-ongkir')
            ->assertOk()
            ->assertSee('Cek Ongkir')
            ->assertSee('Lokasi Asal (Toko)')
            ->assertSee('Lokasi Tujuan')
            ->assertSee('Kecamatan Tujuan');
    }

    public function test_provinsi_dinormalkan_dari_api_v2(): void
    {
        Http::fake([
            '*' => Http::response($this->provinceResponse()),
        ]);

        $this->getJson('/provinces')
            ->assertOk()
            ->assertJsonPath('rajaongkir.status.code', 200)
            ->assertJsonCount(2, 'rajaongkir.results')
            ->assertJsonPath('rajaongkir.results.0.province_id', 13)
            ->assertJsonPath('rajaongkir.results.0.province', 'JAWA TENGAH');

        Http::assertSent(function (ClientRequest $request) {
            return $request->url() === 'https://rajaongkir.komerce.id/api/v1/destination/province'
                && $request->hasHeader('key', 'test-key');
        });
    }

    public function test_kota_diambil_berdasarkan_id_provinsi(): void
    {
        Http::fake([
            '*' => Http::response($this->cityResponse()),
        ]);

        $this->getJson('/cities?province_id=13')
            ->assertOk()
            ->assertJsonPath('rajaongkir.results.0.city_id', 501)
            ->assertJsonPath('rajaongkir.results.0.city_name', 'KOTA SEMARANG');

        Http::assertSent(function (ClientRequest $request) {
            return $request->url() === 'https://rajaongkir.komerce.id/api/v1/destination/city/13'
                && $request->hasHeader('key', 'test-key');
        });
    }

    public function test_kecamatan_diambil_berdasarkan_id_kota(): void
    {
        Http::fake([
            '*' => Http::response($this->districtResponse()),
        ]);

        $this->getJson('/districts?city_id=501')
            ->assertOk()
            ->assertJsonPath('rajaongkir.results.0.district_id', 3855)
            ->assertJsonPath('rajaongkir.results.0.district_name', 'DIWEK');

        Http::assertSent(function (ClientRequest $request) {
            return $request->url() === 'https://rajaongkir.komerce.id/api/v1/destination/district/501'
                && $request->hasHeader('key', 'test-key');
        });
    }

    public function test_biaya_dihitung_dengan_parameter_form_dan_dinormalkan(): void
    {
        Http::fake([
            '*' => Http::response($this->costResponse()),
        ]);

        $this->postJson('/cost', [
            'origin' => 3855,
            'destination' => 1376,
            'weight' => 1000,
            'courier' => 'jne',
        ])
            ->assertOk()
            ->assertJsonPath('rajaongkir.status.code', 200)
            ->assertJsonCount(2, 'rajaongkir.results')
            ->assertJsonPath('rajaongkir.results.0.code', 'jne')
            ->assertJsonPath('rajaongkir.results.0.service', 'REG')
            ->assertJsonPath('rajaongkir.results.0.cost', 18000)
            ->assertJsonPath('rajaongkir.results.0.etd', '2-3');

        Http::assertSent(function (ClientRequest $request) {
            return $request->url() === 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost'
                && $request->isForm()
                && (string) $request['origin'] === '3855'
                && (string) $request['destination'] === '1376'
                && (string) $request['weight'] === '1000'
                && (string) $request['courier'] === 'jne'
                && $request->hasHeader('key', 'test-key');
        });
    }

    public function test_parameter_biaya_tidak_valid_ditolak(): void
    {
        $this->postJson('/cost', [
            'origin' => 3855,
            'courier' => 'invalid',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'weight', 'courier']);
    }

    public function test_api_key_kosong_menghasilkan_pesan_yang_jelas(): void
    {
        config(['services.rajaongkir.api_key' => null]);

        $this->getJson('/provinces')
            ->assertStatus(503)
            ->assertJsonPath('rajaongkir.status.code', 503)
            ->assertJsonPath(
                'rajaongkir.status.description',
                'RAJAONGKIR_API_KEY belum diatur. Isi API key di file .env terlebih dahulu.'
            );

        Http::assertNothingSent();
    }

    public function test_error_dari_api_diteruskan_dengan_pesan_yang_jelas(): void
    {
        Http::fake([
            '*' => Http::response([
                'meta' => [
                    'message' => 'Invalid Api key, key not found',
                    'code' => 400,
                    'status' => 'failed',
                ],
                'data' => null,
            ], 400),
        ]);

        $this->getJson('/provinces')
            ->assertStatus(400)
            ->assertJsonPath('rajaongkir.status.code', 400)
            ->assertJsonPath('rajaongkir.status.description', 'Invalid Api key, key not found');
    }

    private function provinceResponse(): array
    {
        return [
            'meta' => ['message' => 'Success Get Province', 'code' => 200, 'status' => 'success'],
            'data' => [
                ['id' => 13, 'name' => 'JAWA TENGAH'],
                ['id' => 11, 'name' => 'DKI JAKARTA'],
            ],
        ];
    }

    private function cityResponse(): array
    {
        return [
            'meta' => ['message' => 'Success Get City', 'code' => 200, 'status' => 'success'],
            'data' => [
                ['id' => 501, 'name' => 'KOTA SEMARANG', 'zip_code' => '50100'],
            ],
        ];
    }

    private function districtResponse(): array
    {
        return [
            'meta' => ['message' => 'Success Get District', 'code' => 200, 'status' => 'success'],
            'data' => [
                ['id' => 3855, 'name' => 'DIWEK', 'zip_code' => '61471'],
            ],
        ];
    }

    private function costResponse(): array
    {
        return [
            'meta' => ['message' => 'Success Calculate Cost', 'code' => 200, 'status' => 'success'],
            'data' => [
                [
                    'code' => 'jne',
                    'service' => 'REG',
                    'description' => 'Layanan Reguler',
                    'cost' => 18000,
                    'currency' => 'IDR',
                    'etd' => '2-3',
                    'note' => '',
                ],
                [
                    'code' => 'jne',
                    'service' => 'YES',
                    'description' => 'Yakin Esok Sampai',
                    'cost' => 32000,
                    'currency' => 'IDR',
                    'etd' => '1-1',
                    'note' => '',
                ],
            ],
        ];
    }
}
