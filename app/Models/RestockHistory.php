<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'jumlah',
        'kedaluwarsa',
    ];

    // Relasi ke User (Supplier)
    public function supplier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}