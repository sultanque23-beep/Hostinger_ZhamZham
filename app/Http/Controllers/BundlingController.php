<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bundling;       
use App\Models\BundlingDetail; 
use Illuminate\Support\Facades\DB;

class BundlingController extends Controller
{
    public function index(Request $request)
    {
        // Query dasar mengambil data bundling beserta relasi produknya
        $query = Bundling::with('details.product');

        // 1. FILTER OTOMATIS RENCENG (6-15 PCS) & DUS (24-48 PCS)
        if ($request->filter === 'renceng') {
            $query->whereRaw('(SELECT COALESCE(SUM(qty), 0) FROM bundling_details WHERE bundling_details.bundling_id = bundlings.id) BETWEEN 6 AND 15');
        } elseif ($request->filter === 'dus') {
            $query->whereRaw('(SELECT COALESCE(SUM(qty), 0) FROM bundling_details WHERE bundling_details.bundling_id = bundlings.id) BETWEEN 24 AND 48');
        }

        // 2. Fitur Pencarian berdasarkan nama paket atau kode paket
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('bundle_code', 'like', '%' . $search . '%');
            });
        }

        $bundlings = $query->latest()->get();

        return view('bundling.index', compact('bundlings'));
    }
    
    public function create()
    {
        $products = Product::all();
        return view('bundling.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bundle_name'  => 'required|string|max:255',
            'bundle_price' => 'required|numeric|min:0',
            'product_id'   => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'qty'          => 'required|array',
            'qty.*'        => 'required|integer|min:1',
        ]);

        // Hitung total qty seluruh barang yang dimasukkan
        $totalQty = array_sum($request->qty);
        $bundleName = $request->bundle_name;

        // Otomasi Penambahan Prefix Nama "1 Renceng" / "1 Dus" jika belum ditulis
        if ($totalQty >= 6 && $totalQty <= 15) {
            if (!\Str::contains(strtolower($bundleName), 'renceng')) {
                $bundleName = '1 Renceng ' . $bundleName;
            }
        } elseif ($totalQty >= 24 && $totalQty <= 48) {
            if (!\Str::contains(strtolower($bundleName), 'dus') && !\Str::contains(strtolower($bundleName), 'karton')) {
                $bundleName = '1 Dus ' . $bundleName;
            }
        }

        DB::beginTransaction();
        try {
            $bundling = Bundling::create([
                'bundle_code'  => 'BND-PAKET-' . rand(100, 999), 
                'name'         => $bundleName,
                'bundle_price' => $request->bundle_price,
            ]);

            foreach ($request->product_id as $index => $prodId) {
                BundlingDetail::create([
                    'bundling_id' => $bundling->id,
                    'product_id'  => $prodId,
                    'qty'         => $request->qty[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('bundling.index')->with('success', 'Strategi promosi paket bundling berhasil dipublikasikan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses paket bundling: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $bundling = Bundling::with('details.product')->findOrFail($id);
        $products = Product::all();
        
        return view('bundling.edit', compact('bundling', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bundle_name'  => 'required|string|max:255',
            'bundle_price' => 'required|numeric|min:0',
            'product_id'   => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'qty'          => 'required|array',
            'qty.*'        => 'required|integer|min:1',
        ]);

        // Hitung total qty seluruh barang saat diperbarui
        $totalQty = array_sum($request->qty);
        $bundleName = $request->bundle_name;

        // Otomasi Penambahan Prefix Nama "1 Renceng" / "1 Dus"
        if ($totalQty >= 6 && $totalQty <= 15) {
            if (!\Str::contains(strtolower($bundleName), 'renceng')) {
                $bundleName = '1 Renceng ' . $bundleName;
            }
        } elseif ($totalQty >= 24 && $totalQty <= 48) {
            if (!\Str::contains(strtolower($bundleName), 'dus') && !\Str::contains(strtolower($bundleName), 'karton')) {
                $bundleName = '1 Dus ' . $bundleName;
            }
        }

        DB::beginTransaction();
        try {
            $bundling = Bundling::findOrFail($id);
            
            // Update data utama bundling
            $bundling->update([
                'name'         => $bundleName,
                'bundle_price' => $request->bundle_price,
            ]);

            // Hapus detail komponen lama
            BundlingDetail::where('bundling_id', $bundling->id)->delete();

            // Masukkan ulang detail komponen baru dari form edit
            foreach ($request->product_id as $index => $prodId) {
                BundlingDetail::create([
                    'bundling_id' => $bundling->id,
                    'product_id'  => $prodId,
                    'qty'         => $request->qty[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('bundling.index')->with('success', 'Paket bundling berhasil diperbarui!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui paket bundling: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bundling = Bundling::findOrFail($id);
            
            // Hapus detail komponen
            BundlingDetail::where('bundling_id', $bundling->id)->delete();
            
            // Hapus paket utama
            $bundling->delete();

            return redirect()->route('bundling.index')->with('success', 'Paket bundling berhasil dihapus!');
            
        } catch (\Exception $e) {
            return redirect()->route('bundling.index')->with('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }
}