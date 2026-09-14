<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Mengunci nama tabel secara mutlak ke MySQL
    protected $table = 'products';

    // Mengizinkan pengisian data secara aman
    protected $fillable = [
        'product_code', 
        'name', 
        'stock', 
        'purchase_price', 
        'selling_price', 
        'wholesale_price',    // <--- Ditambahkan
        'wholesale_min_qty', // <--- Ditambahkan
        'expired_at', 
        'expired_date'
    ];

    protected $casts = [
        'expired_at' => 'date',
    ];
}