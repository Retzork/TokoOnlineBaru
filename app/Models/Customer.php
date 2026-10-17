<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    protected $table = 'customer';

    /**
     * Kolom yang benar-benar ada di tabel customer.
     * (nama, email, status, role, password, hp, foto adalah kolom tabel user)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'google_id',
        'google_token',
        'alamat',
        'pos',
    ];

    /**
     * Relasi ke tabel user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Hapus data user terkait ketika data customer dihapus
        static::deleting(function ($customer) {
            $customer->user()->delete();
        });
    }
}
