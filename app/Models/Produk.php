<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    // Set the table name
    protected $table = "produk";

    // Disallow mass-assignment of the 'id' field
    protected $guarded = ['id'];

    // Define the relationship with Kategori (assuming 'kategori_id' is the foreign key)
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id'); // Assuming the foreign key is 'kategori_id'
    }

    // Define the relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming the foreign key is 'user_id'
    }

    // Define the relationship with FotoProduk (assuming 'produk_id' is the foreign key in FotoProduk)
    public function fotos()
    {
        return $this->hasMany(FotoProduk::class, 'produk_id'); // Assuming 'produk_id' is the foreign key in FotoProduk
    }
}
