<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customerUser;
    private Customer $customer;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Toko',
            'email' => 'admin@example.test',
            'role' => '1',
            'status' => 1,
            'hp' => '081234567890',
            'password' => bcrypt('secret'),
        ]);

        $this->customerUser = User::create([
            'nama' => 'Nailah Customer',
            'email' => 'nailah@example.test',
            'role' => '2',
            'status' => 1,
            'hp' => '081298765432',
            'password' => bcrypt('secret'),
        ]);

        $this->customer = Customer::create([
            'user_id' => $this->customerUser->id,
            'alamat' => 'Jl. Pelanggan No. 1',
            'pos' => '50100',
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Kue Basah']);

        $this->produk = Produk::create([
            'kategori_id' => $kategori->id,
            'user_id' => $this->admin->id,
            'status' => 1,
            'nama_produk' => 'Kue Lapis Tradisional',
            'detail' => '<p>Kue lapis legit.</p>',
            'harga' => 25000,
            'stok' => 40,
            'berat' => 250,
            'foto' => 'kue-lapis.jpg',
        ]);
    }

    private function makeOrder(string $status, array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'customer_id' => $this->customer->id,
            'user_id' => $this->customerUser->id,
            'status' => $status,
            'total_harga' => 50000,
            'biaya_ongkir' => 18000,
            'kurir' => 'JNE',
            'layanan_ongkir' => 'REG',
            'estimasi_ongkir' => '2-3',
            'total_berat' => 500,
            'alamat' => 'Jl. Pengiriman No. 10',
            'pos' => '50100',
            'noresi' => null,
            'tanggal' => now(),
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'produk_id' => $this->produk->id,
            'quantity' => 2,
            'harga' => 25000,
        ]);

        return $order;
    }

    public function test_tamu_tidak_bisa_membuka_manajemen_pesanan(): void
    {
        $this->get(route('pesanan.proses'))
            ->assertRedirect(route('backend.login'));

        $this->get(route('pesanan.selesai'))
            ->assertRedirect(route('backend.login'));
    }

    public function test_admin_melihat_pesanan_proses_saja(): void
    {
        $paid = $this->makeOrder('Paid');
        $kirim = $this->makeOrder('Kirim', ['noresi' => 'JNE-123']);
        $selesai = $this->makeOrder('Selesai');
        $pending = $this->makeOrder('pending');

        $this->actingAs($this->admin)
            ->get(route('pesanan.proses'))
            ->assertOk()
            ->assertSee('Pesanan Proses')
            ->assertSee('#'.$paid->id)
            ->assertSee('#'.$kirim->id)
            ->assertSee('JNE-123')
            ->assertDontSee(route('pesanan.detail', $selesai->id))
            ->assertDontSee(route('pesanan.detail', $pending->id));
    }

    public function test_admin_melihat_pesanan_selesai_saja(): void
    {
        $selesai = $this->makeOrder('Selesai');

        $this->actingAs($this->admin)
            ->get(route('pesanan.selesai'))
            ->assertOk()
            ->assertSee('Pesanan Selesai')
            ->assertSee('#'.$selesai->id)
            ->assertSee('Nailah Customer');
    }

    public function test_admin_membuka_detail_pesanan(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->admin)
            ->get(route('pesanan.detail', $order->id))
            ->assertOk()
            ->assertSee('Detail Pesanan #'.$order->id)
            ->assertSee('Nailah Customer')
            ->assertSee('nailah@example.test')
            ->assertSee('Kue Lapis Tradisional')
            ->assertSee('Rp. 68.000');
    }

    public function test_admin_memperbarui_status_dan_resi_pesanan(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->admin)
            ->put(route('pesanan.update', $order->id), [
                'status' => 'Kirim',
                'noresi' => 'JNE-001',
                'alamat' => 'Jl. Alamat Baru No. 2',
                'pos' => '50200',
            ])
            ->assertRedirect(route('pesanan.proses'))
            ->assertSessionHas('success', 'Data pesanan berhasil diperbarui');

        $this->assertDatabaseHas('order', [
            'id' => $order->id,
            'status' => 'Kirim',
            'noresi' => 'JNE-001',
            'alamat' => 'Jl. Alamat Baru No. 2',
            'pos' => '50200',
        ]);
    }

    public function test_status_kirim_wajib_memiliki_nomor_resi(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->admin)
            ->put(route('pesanan.update', $order->id), [
                'status' => 'Kirim',
                'noresi' => '',
                'alamat' => 'Jl. Alamat Baru No. 2',
                'pos' => '50200',
            ])
            ->assertSessionHasErrors('noresi');

        $this->assertDatabaseHas('order', [
            'id' => $order->id,
            'status' => 'Paid',
        ]);
    }

    public function test_status_pesanan_tidak_valid_ditolak(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->admin)
            ->put(route('pesanan.update', $order->id), [
                'status' => 'Lunas',
                'alamat' => 'Jl. Alamat Baru No. 2',
                'pos' => '50200',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_membuka_invoice_pesanan(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->admin)
            ->get(route('pesanan.invoice', $order->id))
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('INV-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT))
            ->assertSee('Nailah Customer')
            ->assertSee('Kue Lapis Tradisional')
            ->assertSee('TOTAL BAYAR');
    }

    public function test_customer_hanya_bisa_membuka_invoice_miliknya(): void
    {
        $order = $this->makeOrder('Paid');

        $this->actingAs($this->customerUser)
            ->get(route('order.invoice', $order->id))
            ->assertOk()
            ->assertSee('INVOICE');

        $otherUser = User::create([
            'nama' => 'Customer Lain',
            'email' => 'lain@example.test',
            'role' => '2',
            'status' => 1,
            'hp' => '081200000000',
            'password' => bcrypt('secret'),
        ]);
        $otherCustomer = Customer::create(['user_id' => $otherUser->id]);
        $otherOrder = $this->makeOrder('Paid', ['customer_id' => $otherCustomer->id]);

        $this->actingAs($this->customerUser)
            ->get(route('order.invoice', $otherOrder->id))
            ->assertNotFound();
    }

    public function test_form_laporan_pesanan_dapat_dibuka(): void
    {
        $this->actingAs($this->admin)
            ->get(route('backend.laporan.formpesananproses'))
            ->assertOk()
            ->assertSee('Laporan Pesanan Proses');

        $this->actingAs($this->admin)
            ->get(route('backend.laporan.formpesananselesai'))
            ->assertOk()
            ->assertSee('Laporan Pesanan Selesai');
    }

    public function test_laporan_pesanan_proses_memfilter_tanggal(): void
    {
        $inside = $this->makeOrder('Paid');
        $outside = $this->makeOrder('Kirim', ['noresi' => 'JNE-999']);
        Order::whereKey($outside->id)->update(['created_at' => now()->subMonth()]);

        $this->actingAs($this->admin)
            ->post(route('backend.laporan.cetakpesananproses'), [
                'tanggal_awal' => now()->toDateString(),
                'tanggal_akhir' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertSee('<td>#'.$inside->id.'</td>', false)
            ->assertDontSee('<td>#'.$outside->id.'</td>', false)
            ->assertSee('Total Nilai Pesanan Proses');
    }

    public function test_laporan_pesanan_selesai_menghitung_pendapatan(): void
    {
        $this->makeOrder('Selesai');
        $outside = $this->makeOrder('Selesai');
        Order::whereKey($outside->id)->update(['created_at' => now()->subMonth()]);

        $this->actingAs($this->admin)
            ->post(route('backend.laporan.cetakpesananselesai'), [
                'tanggal_awal' => now()->toDateString(),
                'tanggal_akhir' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertSee('Laporan Pesanan Selesai')
            ->assertSee('Total Pendapatan')
            ->assertSee('Rp. 68.000');
    }

    public function test_rentang_tanggal_laporan_divalidasi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('backend.laporan.cetakpesananproses'), [
                'tanggal_awal' => now()->toDateString(),
                'tanggal_akhir' => now()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('tanggal_akhir');
    }

    public function test_checkout_customer_menghasilkan_status_paid(): void
    {
        $order = $this->makeOrder('pending');

        $this->actingAs($this->customerUser)
            ->post(route('order.checkoutProses'))
            ->assertRedirect(route('order.history'));

        $this->assertDatabaseHas('order', [
            'id' => $order->id,
            'status' => 'Paid',
        ]);
    }
}
