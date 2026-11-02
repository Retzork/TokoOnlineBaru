<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $weight = $request->input('weight');
        $courier = $request->input('courier');

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
            $order->status = 'proses'; // Status berubah dari pending ke proses (sesuai modul history)
            $order->tanggal = now();
            $order->save();

            return redirect()->route('order.history')->with('success', 'Pesanan berhasil dibuat!');
        }

        return redirect()->route('order.cart')->with('error', 'Gagal memproses pesanan');
    }

    public function history()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $orders = Order::where('customer_id', $customer->id)
            ->where('status', '!=', 'pending')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('v_order.history', compact('orders'));
    }

    /*
    |--------------------------------------------------------------------------
    | Minggu 10 - Manajemen Pemesanan (backend)
    |--------------------------------------------------------------------------
    */

    /**
     * Daftar pesanan yang masih diproses (proses / Paid / Kirim).
     */
    public function statusProses()
    {
        $order = Order::with(['customer.user', 'orderItems.produk'])
            ->whereIn('status', ['proses', 'Paid', 'Kirim'])
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
        $order = Order::with(['customer.user', 'orderItems.produk'])
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
     * Detail satu pesanan beserta form perubahan status.
     */
    public function statusDetail($id)
    {
        $order = Order::with(['customer.user', 'orderItems.produk'])->findOrFail($id);

        return view('backend.v_pesanan.detail', [
            'judul' => 'Pesanan',
            'subJudul' => 'Detail Pesanan #' . $order->id,
            'order' => $order,
        ]);
    }

    /**
     * Menyimpan perubahan status / no resi / alamat / kode pos pesanan.
     */
    public function statusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $rules = [
            'alamat' => 'required',
        ];

        if ($request->status != $order->status) {
            $rules['status'] = 'required|in:proses,Paid,Kirim,Selesai';
        }
        if ($request->noresi != $order->noresi) {
            $rules['noresi'] = 'required';
        }
        if ($request->pos != $order->pos) {
            $rules['pos'] = 'required';
        }

        $messages = [
            'alamat.required' => 'Alamat pengiriman wajib diisi.',
            'status.required' => 'Status pesanan wajib dipilih.',
            'status.in' => 'Status pesanan tidak dikenali.',
            'noresi.required' => 'Nomor resi wajib diisi.',
            'pos.required' => 'Kode pos wajib diisi.',
        ];

        $validatedData = $request->validate($rules, $messages);
        Order::where('id', $id)->update($validatedData);

        return redirect()->route('pesanan.proses')
            ->with('success', 'Data pesanan berhasil diperbaharui');
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
     * Cetak laporan pesanan proses per rentang tanggal.
     */
    public function cetakOrderProses(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $order = Order::with(['customer.user', 'orderItems.produk'])
            ->whereIn('status', ['proses', 'Paid', 'Kirim'])
            ->whereBetween('created_at', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59'])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.v_pesanan.cetakproses', [
            'judul' => 'Laporan Pesanan Proses',
            'subJudul' => 'Laporan Pesanan Proses',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
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
     * Cetak laporan pesanan selesai + total pendapatan.
     */
    public function cetakOrderSelesai(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $order = Order::with(['customer.user', 'orderItems.produk'])
            ->where('status', 'Selesai')
            ->whereBetween('created_at', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59'])
            ->orderBy('id', 'desc')
            ->get();

        $totalPendapatan = $order->sum(function ($row) {
            return $row->total_harga + $row->biaya_ongkir;
        });

        return view('backend.v_pesanan.cetakselesai', [
            'judul' => 'Laporan Pesanan Selesai',
            'subJudul' => 'Laporan Pesanan Selesai',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $order,
            'totalPendapatan' => $totalPendapatan,
        ]);
    }

    /**
     * Invoice pesanan dari sisi backend (admin).
     */
    public function invoiceBackend($id)
    {
        $order = Order::with(['customer.user', 'orderItems.produk'])->findOrFail($id);

        return view('backend.v_pesanan.invoice', [
            'judul' => 'Invoice Pesanan #' . $order->id,
            'order' => $order,
        ]);
    }

    /**
     * Invoice pesanan dari sisi customer (hanya pesanan miliknya sendiri).
     */
    public function invoiceFrontend($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::with(['customer.user', 'orderItems.produk'])
            ->where('id', $id)
            ->where('customer_id', $customer->id ?? 0)
            ->firstOrFail();

        return view('v_order.invoice', [
            'judul' => 'Invoice Pesanan #' . $order->id,
            'order' => $order,
        ]);
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
