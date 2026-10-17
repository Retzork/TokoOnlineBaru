<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoProduk extends Model
{
    use HasFactory;

    // Set the table name
    protected $table = "foto_produk"; // Assuming the table is named 'foto_produk'

    // Disallow mass-assignment of 'id' field
    protected $guarded = ['id'];

    // Define the relationship with the Produk model
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id'); // Assuming 'produk_id' is the foreign key
    }

    // Define the relationship with Kategori (if applicable)
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    // Define the relationship with User (if applicable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
