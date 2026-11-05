<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class BerandaController extends Controller
{
    public function berandaBackend()
    {
        $tahun = (int) now()->year;

        // Ringkasan data untuk kartu statistik dashboard.
        $totalProduk = Produk::count();
        $totalCustomer = Customer::count();
        $pesananProses = Order::whereIn('status', ['proses', 'Paid', 'Kirim'])->count();
        $pesananSelesai = Order::where('status', 'Selesai')->count();
        $totalPendapatan = (float) Order::where('status', 'Selesai')->sum('total_harga')
            + (float) Order::where('status', 'Selesai')->sum('biaya_ongkir');

        // Grafik pesanan per bulan (Minggu 11).
        $rekap = Order::selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->whereYear('created_at', $tahun)
            ->where('status', '!=', 'pending')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $grafikPesanan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $grafikPesanan[] = [
                Carbon::create($tahun, $bulan, 1)->translatedFormat('M'),
                (int) ($rekap[$bulan] ?? 0),
            ];
        }

        return view('backend.v_beranda.index', [
            'judul' => 'Halaman Beranda',
            'tahun' => $tahun,
            'totalProduk' => $totalProduk,
            'totalCustomer' => $totalCustomer,
            'pesananProses' => $pesananProses,
            'pesananSelesai' => $pesananSelesai,
            'totalPendapatan' => $totalPendapatan,
            'grafikPesanan' => $grafikPesanan,
        ]);
    }

    public function index()
    {
        $produk = Produk::where('status', 1)->orderBy('updated_at', 'desc')->paginate(6);
        return view('v_beranda.index', ['judul' => 'Halaman Beranda', 'produk' => $produk]);
    }
}