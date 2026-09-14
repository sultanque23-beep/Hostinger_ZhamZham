<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundling extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi
    protected $fillable = ['bundle_code', 'name', 'bundle_price'];

    // Relasi ke detail bundling
    public function details()
    {
        return $this->hasMany(BundlingDetail::class, 'bundling_id');
    }
}