<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = true;

    protected $table = "order";

    protected $fillable = [
        'customer_id',
        'user_id',
        'total_harga',
        'status',
        'noresi',
        'kurir',
        'layanan_ongkir',
        'biaya_ongkir',
        'estimasi_ongkir',
        'total_berat',
        'alamat',
        'pos',
        'tanggal',
    ];

    /**
     * Label status yang ditampilkan di halaman customer maupun backend.
     * Status "proses" adalah data lama dari checkout sebelum Minggu 10.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'Paid', 'paid', 'proses' => 'Diproses',
            'Kirim' => 'Dikirim',
            'Selesai' => 'Selesai',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Kelas badge Bootstrap untuk status pesanan.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'Paid', 'paid', 'proses' => 'badge-primary',
            'Kirim' => 'badge-info',
            'Selesai' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    /**
     * Total yang harus dibayar: subtotal produk + ongkos kirim.
     */
    public function getTotalBayarAttribute(): float
    {
        return (float) $this->total_harga + (float) $this->biaya_ongkir;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
