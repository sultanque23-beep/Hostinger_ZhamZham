<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    // INI YANG PALING PENTING:
    protected $fillable = [
        'transaction_id', 
        'product_id', 
        'quantity', 
        'price', 
       
    ];

    // Relasi untuk mengambil nama barang di struk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}