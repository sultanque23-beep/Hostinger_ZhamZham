<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RestockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('supplier.stok.index', compact('products'));
    }

    public function addStock(Request $request, $id)
    {
        $request->validate([
            'jumlah_stok' => 'required|integer|min:1',
            'kedaluwarsa'  => 'nullable|date',
        ]);

        $product = Product::findOrFail($id);
        $jumlah = (int) $request->input('jumlah_stok');

        // 1. Update Stok Produk
        if (isset($product->stock)) {
            $product->stock += $jumlah;
        } else {
            $product->stok = ($product->stok ?? 0) + $jumlah;
        }

        if ($request->filled('kedaluwarsa')) {
            if (isset($product->tanggal_kedaluwarsa)) {
                $product->tanggal_kedaluwarsa = $request->kedaluwarsa;
            } elseif (isset($product->expired_date)) {
                $product->expired_date = $request->kedaluwarsa;
            } else {
                $product->kedaluwarsa = $request->kedaluwarsa;
            }
        }

        $product->save();

        // 2. Simpan Catatan Riwayat Masuk
        RestockHistory::create([
            'user_id'    => Auth::id(),
            'product_id' => $product->id,
            'jumlah'     => $jumlah,
            'kedaluwarsa'=> $request->input('kedaluwarsa'),
        ]);

        $namaBarang = $product->nama_barang ?? $product->name ?? 'Barang';

        return redirect()->back()->with('success', "Stok {$namaBarang} berhasil ditambah sebanyak {$jumlah} dan dicatat dalam riwayat!");
    }
}