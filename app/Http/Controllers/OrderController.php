<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Menambahkan produk ke keranjang (order dengan status pending).
     */
    public function addToCart($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            return redirect()->route('auth.redirect')
                ->with('msgError', 'Data customer tidak ditemukan, silakan login ulang');
        }

        $produk = Produk::findOrFail($id);

        $order = Order::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'pending'],
            ['total_harga' => 0]
        );

        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id],
            ['quantity' => 1, 'harga' => $produk->harga]
        );

        if (!$orderItem->wasRecentlyCreated) {
            $orderItem->quantity++;
            $orderItem->save();
        }

        $order->total_harga += $produk->harga;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Menampilkan halaman keranjang belanja customer.
     */
    public function viewCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();
        if ($order) {
            $order->load('orderItems.produk');
        }

        return view('v_order.cart', compact('order'));
    }

    public function updateCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = $order->orderItems()->where('id', $id)->first();
            if ($orderItem) {
                $quantity = $request->input('quantity');
                if ($quantity > $orderItem->produk->stok) {
                    return redirect()->route('order.cart')->with('error', 'Jumlah produk melebihi stok yang tersedia');
                }

                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->quantity = $quantity;
                $orderItem->save();

                $order->total_harga += $orderItem->harga * $orderItem->quantity;
                $order->save();
            }
        }
        return redirect()->route('order.cart')->with('success', 'Jumlah produk berhasil diperbarui');
    }

    public function removeFromCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('produk_id', $id)->first();

            if ($orderItem) {
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->delete();

                if ($order->total_harga <= 0) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }
        }
        return redirect()->route('order.cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function selectShipping(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if (!$order || $order->orderItems->count() == 0) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }

        return view('v_order.select_shipping', compact('order'));
    }

    public function updateongkir(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            // Simpan data ongkir ke dalam order
            $order->kurir = $request->input('kurir');
            $order->layanan_ongkir = $request->input('layanan_ongkir');
            $order->biaya_ongkir = $request->input('biaya_ongkir');
            $order->estimasi_ongkir = $request->input('estimasi_ongkir');
            $order->total_berat = $request->input('total_berat');
            $order->alamat = $request->input('alamat') . ', <br> ' . $request->input('city_name') . ', ' . $request->input('province_name');
            $order->pos = $request->input('pos');
            $order->save();

            return redirect()->route('order.selectpayment');
        }

        return back()->with('error', 'Gagal menyimpan data ongkir');
    }

    // ongkir getProvinces
    public function getProvinces(): JsonResponse
    {
        if ($this->useDummyOngkir()) {
            return $this->dummyResponse($this->dummyProvinces());
        }

        return $this->requestRajaOngkir(
            'v1/destination/province',
            fn (array $item) => [
                'province_id' => $item['id'] ?? $item['province_id'] ?? null,
                'province' => $item['name'] ?? $item['province'] ?? null,
            ]
        );
    }

    // ongkir getCities
    public function getCities(Request $request): JsonResponse
    {
        $provinceId = $request->input('province_id');

        if ($this->useDummyOngkir()) {
            return $this->dummyResponse($this->dummyCities($provinceId));
        }

        return $this->requestRajaOngkir(
            'v1/destination/city/' . $provinceId,
            fn (array $item) => [
                'city_id' => $item['id'] ?? $item['city_id'] ?? null,
                'city_name' => $item['name'] ?? $item['city_name'] ?? null,
            ]
        );
    }

    // ongkir getCost
    public function getCost(Request $request): JsonResponse
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = (int) $request->input('weight');
        $courier = $request->input('courier');

        if ($this->useDummyOngkir()) {
            return $this->dummyResponse($this->dummyCost($courier, $weight, $origin, $destination));
        }

        $payload = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ];

        return $this->requestRajaOngkir(
            'v1/calculate/domestic-cost',
            function (array $payload) {
                $rates = $payload['data'] ?? [];

                // Jika respons V2 mengembalikan data tunggal, ubah ke array
                if (isset($rates['service'])) {
                    $rates = [$rates];
                }

                return array_map(function ($rate) {
                    return [
                        'service' => $rate['service'] ?? '-',
                        'description' => $rate['description'] ?? '',
                        'cost' => is_array($rate['cost']) ? $rate['cost'] : [['value' => $rate['cost'] ?? 0, 'etd' => $rate['etd'] ?? '']],
                    ];
                }, array_filter((array)$rates, 'is_array'));
            },
            'POST',
            $payload
        );
    }

    public function selectpayment()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if (!$order) {
            return redirect()->route('order.cart');
        }

        return view('v_order.select_payment', compact('order'));
    }

    public function checkoutProses(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            // Tanpa payment gateway (Minggu 12), tombol "Bayar Sekarang"
            // dianggap berhasil membayar sehingga masuk ke antrean pesanan proses.
            $order->status = 'Paid';
            $order->tanggal = now();
            $order->save();

            return redirect()->route('order.history')->with('success', 'Pesanan berhasil dibuat!');
        }

        return redirect()->route('order.cart')->with('error', 'Gagal memproses pesanan');
    }

    public function history()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return redirect()->route('auth.redirect')
                ->with('msgError', 'Data customer tidak ditemukan, silakan login ulang');
        }

        $orders = Order::where('customer_id', $customer->id)
            ->where('status', '!=', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        return view('v_order.history', compact('orders'));
    }

    /**
     * Daftar pesanan yang sedang diproses (Paid / Kirim).
     * Status "proses" ikut ditampilkan agar data lama tetap terlihat.
     */
    public function statusProses()
    {
        $order = Order::with(['customer.user'])
            ->whereIn('status', ['Paid', 'proses', 'Kirim'])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.v_pesanan.proses', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'index' => $order,
        ]);
    }

    /**
     * Daftar pesanan yang sudah selesai.
     */
    public function statusSelesai()
    {
        $order = Order::with(['customer.user'])
            ->where('status', 'Selesai')
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.v_pesanan.selesai', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Selesai',
            'index' => $order,
        ]);
    }

    /**
     * Detail satu pesanan untuk admin.
     */
    public function statusDetail($id)
    {
        $order = Order::with(['customer.user', 'orderItems.produk.kategori'])
            ->findOrFail($id);

        return view('backend.v_pesanan.detail', [
            'judul' => 'Pesanan',
            'subJudul' => 'Detail Pesanan',
            'order' => $order,
        ]);
    }

    /**
     * Perbarui status, nomor resi, alamat dan kode pos pesanan.
     */
    public function statusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'required|in:Paid,Kirim,Selesai',
            'noresi' => 'nullable|required_if:status,Kirim|string|max:255',
            'alamat' => 'required|string',
            'pos' => 'nullable|string|max:10',
        ], [
            'status.required' => 'Status pesanan harus dipilih.',
            'status.in' => 'Status pesanan tidak valid.',
            'noresi.required_if' => 'Nomor resi wajib diisi ketika status pesanan Dikirim.',
            'alamat.required' => 'Alamat pengiriman harus diisi.',
        ]);

        $order->update($validatedData);

        return redirect()->route('pesanan.proses')
            ->with('success', 'Data pesanan berhasil diperbarui');
    }

    /**
     * Invoice dari sisi backend (dibuka admin).
     */
    public function invoiceBackend($id)
    {
        $order = $this->loadOrderForInvoice($id);

        return view('backend.v_pesanan.invoice', [
            'judul' => 'Pesanan',
            'subJudul' => 'Invoice Pesanan',
            'order' => $order,
            'backUrl' => route('pesanan.detail', $order->id),
            'backLabel' => 'Kembali ke Detail Pesanan',
        ]);
    }

    /**
     * Invoice dari sisi customer. Hanya boleh membuka invoice miliknya sendiri.
     */
    public function invoiceFrontend($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            abort(404);
        }

        $order = Order::with(['customer.user', 'orderItems.produk.kategori'])
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return view('v_order.invoice', [
            'judul' => 'Invoice',
            'order' => $order,
            'backUrl' => route('order.history'),
            'backLabel' => 'Kembali ke Riwayat Pesanan',
        ]);
    }

    /**
     * Form laporan pesanan proses.
     */
    public function formOrderProses()
    {
        return view('backend.v_pesanan.formproses', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Proses',
        ]);
    }

    /**
     * Cetak laporan pesanan proses berdasarkan rentang tanggal.
     */
    public function cetakOrderProses(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir] = $this->validasiRentangTanggal($request);

        $order = Order::with(['customer.user'])
            ->whereIn('status', ['Paid', 'proses', 'Kirim'])
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.v_pesanan.cetakproses', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Proses',
            'tanggalAwal' => $request->input('tanggal_awal'),
            'tanggalAkhir' => $request->input('tanggal_akhir'),
            'cetak' => $order,
        ]);
    }

    /**
     * Form laporan pesanan selesai.
     */
    public function formOrderSelesai()
    {
        return view('backend.v_pesanan.formselesai', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Selesai',
        ]);
    }

    /**
     * Cetak laporan pesanan selesai beserta total pendapatan.
     */
    public function cetakOrderSelesai(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir] = $this->validasiRentangTanggal($request);

        $order = Order::with(['customer.user'])
            ->where('status', 'Selesai')
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('id', 'desc')
            ->get();

        $totalPendapatan = $order->sum(function ($row) {
            return (float) $row->total_harga + (float) $row->biaya_ongkir;
        });

        return view('backend.v_pesanan.cetakselesai', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Selesai',
            'tanggalAwal' => $request->input('tanggal_awal'),
            'tanggalAkhir' => $request->input('tanggal_akhir'),
            'cetak' => $order,
            'totalPendapatan' => $totalPendapatan,
        ]);
    }

    /**
     * Validasi dan normalisasi rentang tanggal laporan.
     */
    private function validasiRentangTanggal(Request $request): array
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        return [
            Carbon::parse($request->input('tanggal_awal'))->startOfDay(),
            Carbon::parse($request->input('tanggal_akhir'))->endOfDay(),
        ];
    }

    /**
     * Eager load relasi yang dibutuhkan invoice.
     */
    private function loadOrderForInvoice($id): Order
    {
        return Order::with(['customer.user', 'orderItems.produk.kategori'])
            ->findOrFail($id);
    }

    /**
     * HTTP client RajaOngkir; menggunakan config() dan bypass SSL jika diatur.
     */
    private function client(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/') . '/')
            ->withHeaders(['key' => (string) config('services.rajaongkir.api_key')])
            ->acceptJson()
            ->timeout((int) config('services.rajaongkir.timeout', 30));

        if (config('services.rajaongkir.verify') === false) {
            $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * Eksekusi request + validasi respons V2.
     */
    private function requestRajaOngkir(string $endpoint, Closure $normalizer, string $method = 'GET', array $params = []): JsonResponse
    {
        if (blank(config('services.rajaongkir.api_key'))) {
            return $this->errorResponse('RAJAONGKIR_API_KEY belum diatur.', 503);
        }

        try {
            $client = $this->client();
            $response = ($method === 'POST')
                ? $client->asForm()->post($endpoint, $params)
                : $client->get($endpoint);
        } catch (ConnectionException $exception) {
            Log::error('Gagal terhubung ke RajaOngkir: ' . $exception->getMessage());
            return $this->errorResponse('Gagal terhubung ke API RajaOngkir.', 502);
        }

        $payload = $response->json();

        if (!is_array($payload) || !isset($payload['meta']['code'])) {
            return $this->errorResponse('Respons API tidak dikenali.', 502);
        }

        if ((int)$payload['meta']['code'] !== 200) {
            return $this->errorResponse($payload['meta']['message'] ?? 'Error API.', 502);
        }

        // Mapping data agar sesuai format yang diharapkan View
        $items = $payload['data'] ?? [];
        if (!is_array($items)) $items = [];

        // Jika endpoint cost, data sudah diolah di normalizer
        $results = str_contains($endpoint, 'calculate')
            ? $normalizer($payload)
            : array_map($normalizer, $items);

        return response()->json([
            'rajaongkir' => [
                'status' => ['code' => 200, 'description' => 'OK'],
                'results' => $results
            ]
        ]);
    }

    /**
     * Pakai data dummy jika diaktifkan lewat .env atau API key kosong.
     */
    private function useDummyOngkir(): bool
    {
        return (bool) config('services.rajaongkir.dummy', false)
            || blank(config('services.rajaongkir.api_key'));
    }

    /**
     * Bungkus hasil dummy dengan format yang sama seperti respons asli.
     */
    private function dummyResponse(array $results): JsonResponse
    {
        return response()->json([
            'rajaongkir' => [
                'status' => ['code' => 200, 'description' => 'OK (dummy)'],
                'results' => array_values($results),
            ],
        ]);
    }

    private function dummyProvinces(): array
    {
        return [
            ['province_id' => 1,  'province' => 'DKI Jakarta'],
            ['province_id' => 2,  'province' => 'Jawa Barat'],
            ['province_id' => 3,  'province' => 'Jawa Tengah'],
            ['province_id' => 4,  'province' => 'DI Yogyakarta'],
            ['province_id' => 5,  'province' => 'Jawa Timur'],
            ['province_id' => 6,  'province' => 'Banten'],
            ['province_id' => 7,  'province' => 'Bali'],
            ['province_id' => 8,  'province' => 'Sumatera Utara'],
            ['province_id' => 9,  'province' => 'Sumatera Selatan'],
            ['province_id' => 10, 'province' => 'Kalimantan Timur'],
            ['province_id' => 11, 'province' => 'Sulawesi Selatan'],
        ];
    }

    private function dummyCities($provinceId): array
    {
        $cities = [
            1 => [
                ['city_id' => 101, 'city_name' => 'Jakarta Pusat'],
                ['city_id' => 102, 'city_name' => 'Jakarta Selatan'],
                ['city_id' => 103, 'city_name' => 'Jakarta Barat'],
                ['city_id' => 104, 'city_name' => 'Jakarta Timur'],
                ['city_id' => 105, 'city_name' => 'Jakarta Utara'],
            ],
            2 => [
                ['city_id' => 201, 'city_name' => 'Bandung'],
                ['city_id' => 202, 'city_name' => 'Bekasi'],
                ['city_id' => 203, 'city_name' => 'Bogor'],
                ['city_id' => 204, 'city_name' => 'Depok'],
                ['city_id' => 205, 'city_name' => 'Cirebon'],
            ],
            3 => [
                ['city_id' => 301, 'city_name' => 'Semarang'],
                ['city_id' => 302, 'city_name' => 'Surakarta (Solo)'],
                ['city_id' => 303, 'city_name' => 'Magelang'],
                ['city_id' => 304, 'city_name' => 'Pekalongan'],
            ],
            4 => [
                ['city_id' => 401, 'city_name' => 'Yogyakarta'],
                ['city_id' => 402, 'city_name' => 'Sleman'],
                ['city_id' => 403, 'city_name' => 'Bantul'],
            ],
            5 => [
                ['city_id' => 501, 'city_name' => 'Surabaya'],
                ['city_id' => 502, 'city_name' => 'Malang'],
                ['city_id' => 503, 'city_name' => 'Sidoarjo'],
                ['city_id' => 504, 'city_name' => 'Kediri'],
            ],
            6 => [
                ['city_id' => 601, 'city_name' => 'Tangerang'],
                ['city_id' => 602, 'city_name' => 'Tangerang Selatan'],
                ['city_id' => 603, 'city_name' => 'Serang'],
            ],
            7 => [
                ['city_id' => 701, 'city_name' => 'Denpasar'],
                ['city_id' => 702, 'city_name' => 'Badung'],
                ['city_id' => 703, 'city_name' => 'Gianyar'],
            ],
            8 => [
                ['city_id' => 801, 'city_name' => 'Medan'],
                ['city_id' => 802, 'city_name' => 'Binjai'],
                ['city_id' => 803, 'city_name' => 'Pematangsiantar'],
            ],
            9 => [
                ['city_id' => 901, 'city_name' => 'Palembang'],
                ['city_id' => 902, 'city_name' => 'Prabumulih'],
            ],
            10 => [
                ['city_id' => 1001, 'city_name' => 'Samarinda'],
                ['city_id' => 1002, 'city_name' => 'Balikpapan'],
            ],
            11 => [
                ['city_id' => 1101, 'city_name' => 'Makassar'],
                ['city_id' => 1102, 'city_name' => 'Parepare'],
            ],
        ];

        return $cities[(int) $provinceId] ?? [];
    }

    /**
     * Hitung ongkir dummy: tarif dasar per kurir x berat (dibulatkan ke kg),
     * ditambah faktor jarak sederhana dari selisih ID kota asal & tujuan.
     */
    private function dummyCost($courier, int $weight, $origin, $destination): array
    {
        $kg = max(1, (int) ceil($weight / 1000));
        $jarak = 1 + min(5, abs(((int) $origin) - ((int) $destination)) / 200);

        $tarif = [
            'jne' => [
                ['service' => 'REG', 'description' => 'Layanan Reguler',   'base' => 9000,  'etd' => '2-3 hari'],
                ['service' => 'YES', 'description' => 'Yakin Esok Sampai', 'base' => 18000, 'etd' => '1 hari'],
            ],
            'pos' => [
                ['service' => 'Paket Kilat Khusus', 'description' => 'Paket Kilat Khusus', 'base' => 8000,  'etd' => '3-4 hari'],
                ['service' => 'Express Next Day',   'description' => 'Express Next Day',   'base' => 15000, 'etd' => '1-2 hari'],
            ],
            'tiki' => [
                ['service' => 'REG', 'description' => 'Reguler Service',    'base' => 9500,  'etd' => '2-4 hari'],
                ['service' => 'ONS', 'description' => 'Over Night Service', 'base' => 17000, 'etd' => '1 hari'],
            ],
            'sicepat' => [
                ['service' => 'REG',  'description' => 'Layanan Reguler',      'base' => 8500,  'etd' => '2-3 hari'],
                ['service' => 'BEST', 'description' => 'Besok Sampai Tujuan', 'base' => 16000, 'etd' => '1 hari'],
            ],
        ];

        $layanan = $tarif[strtolower((string) $courier)] ?? $tarif['jne'];

        return array_map(function ($item) use ($kg, $jarak) {
            return [
                'service' => $item['service'],
                'description' => $item['description'],
                'cost' => [[
                    'value' => (int) (round($item['base'] * $kg * $jarak / 500) * 500), // bulatkan ke 500
                    'etd' => $item['etd'],
                ]],
            ];
        }, $layanan);
    }

    /**
     * Format error yang konsisten.
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