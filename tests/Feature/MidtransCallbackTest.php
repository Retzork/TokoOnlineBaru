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

class MidtransCallbackTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test';

    protected function setUp(): void
    {
        parent::setUp();

        config(['midtrans.server_key' => self::SERVER_KEY]);
    }

    public function test_settlement_menandai_pesanan_paid_dan_mengurangi_stok(): void
    {
        [$order, $produk] = $this->makeOrder();

        $this->postJson('/api/midtrans/callback', $this->notification($order))
            ->assertOk()
            ->assertJsonPath('status', 'OK');

        $order->refresh();

        $this->assertSame('Paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('settlement', $order->transaction_status);
        $this->assertSame('bank_transfer', $order->payment_type);
        $this->assertSame('trx-123', $order->transaction_id);
        $this->assertSame(8, $produk->fresh()->stok);
    }

    public function test_notifikasi_ganda_tidak_mengurangi_stok_dua_kali(): void
    {
        [$order, $produk] = $this->makeOrder();

        $this->postJson('/api/midtrans/callback', $this->notification($order))->assertOk();
        $this->postJson('/api/midtrans/callback', $this->notification($order))->assertOk();

        $this->assertSame('Paid', $order->fresh()->status);
        $this->assertSame(8, $produk->fresh()->stok);
    }

    public function test_halaman_pembayaran_memberi_pesan_bila_server_key_kosong(): void
    {
        $this->makeOrder();
        config(['midtrans.server_key' => '']);

        $this->actingAs(User::first())
            ->get('/selectpayment')
            ->assertOk()
            ->assertSee('MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY belum diatur');
    }

    public function test_signature_tidak_valid_ditolak(): void
    {
        [$order, $produk] = $this->makeOrder();

        $this->postJson('/api/midtrans/callback', $this->notification($order, [
            'signature_key' => 'signature-palsu',
        ]))->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_nominal_tidak_sesuai_ditolak(): void
    {
        [$order, $produk] = $this->makeOrder();

        $this->postJson('/api/midtrans/callback', $this->notification($order, [
            'gross_amount' => '1000',
        ]))->assertStatus(422);

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
    }

    /**
     * @return array{0: Order, 1: Produk}
     */
    private function makeOrder(): array
    {
        $user = User::create([
            'nama' => 'Customer Midtrans',
            'email' => 'midtrans@example.test',
            'role' => '2',
            'status' => 1,
            'password' => bcrypt('password'),
            'hp' => '081234567890',
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Makanan']);

        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'user_id' => $user->id,
            'status' => 1,
            'nama_produk' => 'Kue Lapis',
            'detail' => 'Kue lapis tradisional.',
            'harga' => 25000,
            'stok' => 10,
            'berat' => 500,
            'foto' => 'kue-lapis.jpg',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'total_harga' => 50000,
            'biaya_ongkir' => 15000,
            'kurir' => 'JNE',
            'layanan_ongkir' => 'REG',
            'alamat' => 'Jl. Contoh No. 1',
            'pos' => '12345',
            'midtrans_order_id' => '1-' . time() . '-abcd',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'produk_id' => $produk->id,
            'quantity' => 2,
            'harga' => 25000,
        ]);

        return [$order->fresh('orderItems.produk'), $produk];
    }

    private function notification(Order $order, array $overrides = []): array
    {
        $statusCode = (string) ($overrides['status_code'] ?? '200');
        $grossAmount = (string) ($overrides['gross_amount'] ?? $order->grand_total);
        $orderId = (string) ($overrides['order_id'] ?? $order->midtrans_order_id);

        $payload = array_merge([
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'trx-123',
            'signature_key' => hash('sha512', $orderId . $statusCode . $grossAmount . self::SERVER_KEY),
        ], $overrides);

        if (!array_key_exists('signature_key', $overrides)) {
            $payload['signature_key'] = hash('sha512', $orderId . $statusCode . $grossAmount . self::SERVER_KEY);
        }

        return $payload;
    }
}
