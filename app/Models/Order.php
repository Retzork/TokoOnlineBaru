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
        'midtrans_order_id',
        'snap_token',
        'midtrans_redirect_url',
        'payment_type',
        'transaction_id',
        'transaction_status',
        'fraud_status',
        'paid_at',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Total yang harus dibayar (produk + ongkir), dibulatkan ke rupiah.
     */
    public function getGrandTotalAttribute(): int
    {
        return (int) round((float) $this->total_harga + (float) ($this->biaya_ongkir ?? 0));
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
