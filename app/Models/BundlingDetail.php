<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundlingDetail extends Model
{
    use HasFactory;

    protected $fillable = ['bundling_id', 'product_id', 'qty'];

    // Relasi balik ke data produk induk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}