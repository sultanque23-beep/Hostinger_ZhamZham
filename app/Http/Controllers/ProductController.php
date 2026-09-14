<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');
        $enamPuluhHariKeDepan = \Carbon\Carbon::now()->addDays(60)->format('Y-m-d');
        
        // Ambil parameter periode (default: semua)
        $periode = $request->get('periode', 'semua');

        // 1. FILTER PENCARIAN (NAMA BARANG ATAU BARCODE)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        // 2. FILTER BERDASARKAN STATUS / KATEGORI EWS
        if ($request->filter == 'expired') {
            $query->where(function($q) use ($hariIni) {
                $q->where('expired_at', '<', $hariIni)
                  ->orWhere('expired_date', '<', $hariIni);
            });
        } elseif ($request->filter == 'warning') {
            $query->where(function($q) use ($hariIni, $enamPuluhHariKeDepan) {
                $q->whereBetween('expired_at', [$hariIni, $enamPuluhHariKeDepan])
                  ->orWhereBetween('expired_date', [$hariIni, $enamPuluhHariKeDepan]);
            });
        } elseif ($request->filter == 'low_stock') {
            $query->where('stock', '<=', 10);
        } elseif ($request->filter == 'terlaris') {
            $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';
            
            $query->select('products.*')
                  ->selectSub(function ($subQuery) use ($namaTabelDetail, $periode) {
                      $subQuery->from($namaTabelDetail)
                               ->selectRaw('COALESCE(SUM(quantity), 0)')
                               ->whereColumn("{$namaTabelDetail}.product_id", 'products.id');

                      // Filter Tanggal Penjualan (Mingguan / Bulanan)
                      if ($periode == 'mingguan') {
                          $subQuery->whereBetween("{$namaTabelDetail}.created_at", [
                              \Carbon\Carbon::now()->startOfWeek(),
                              \Carbon\Carbon::now()->endOfWeek()
                          ]);
                      } elseif ($periode == 'bulanan') {
                          $subQuery->whereMonth("{$namaTabelDetail}.created_at", \Carbon\Carbon::now()->month)
                                   ->whereYear("{$namaTabelDetail}.created_at", \Carbon\Carbon::now()->year);
                      }
                  }, 'total_terjual')
                  ->having('total_terjual', '>', 0) // <-- HANYA TAMPILKAN BARANG YANG SUDAH TERJUAL (> 0)
                  ->orderByDesc('total_terjual');
        } elseif ($request->filter == 'termahal') {
            $query->orderBy('selling_price', 'desc');
        }

        // 3. PAGINASI DATA (Menyesuaikan Blade dengan query string terpelihara)
        $products = $query->paginate(25)->withQueryString();

        // Ambil data supplier untuk modal restock
        $suppliers = DB::table('suppliers')->orderBy('nama_supplier', 'asc')->get();

        return view('products.index', compact('products', 'suppliers'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code'      => 'required|unique:products,product_code',
            'name'              => 'required',
            'stock'             => 'required|integer',
            'purchase_price'    => 'required|numeric',
            'selling_price'     => 'required|numeric',
            'wholesale_price'   => 'nullable|numeric|min:0',
            'wholesale_min_qty' => 'nullable|integer|min:0',
            'expired_at'        => 'nullable|date',
        ]);

        $data = $request->all();
        $data['expired_date'] = $request->expired_at; 

        Product::create($data);

        return redirect()->route('products.create')->with('success', 'Barang berhasil disimpan! Silakan masukkan barang selanjutnya.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_code'      => 'required|unique:products,product_code,' . $product->id,
            'name'              => 'required',
            'stock'             => 'required|integer',
            'purchase_price'    => 'required|numeric',
            'selling_price'     => 'required|numeric',
            'wholesale_price'   => 'nullable|numeric|min:0',
            'wholesale_min_qty' => 'nullable|integer|min:0',
            'expired_at'        => 'nullable|date',
        ]);

        $data = $request->all();
        $data['expired_date'] = $request->expired_at;

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Barang berhasil diubah!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus!');
    }

    public function restockFromSupplier(Request $request)
    {
        $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'selected_items'   => 'required|array|min:1',
            'qty'              => 'required|array',
        ], [
            'supplier_id.required'    => 'Silakan pilih nama supplier terlebih dahulu!',
            'supplier_id.exists'      => 'Supplier tidak ditemukan di sistem.',
            'selected_items.required' => 'Pilih minimal satu barang untuk di-restock!',
            'selected_items.min'      => 'Pilih minimal satu barang untuk di-restock!',
        ]);

        DB::beginTransaction();
        try {
            $selectedItems = $request->input('selected_items', []);
            $qtys          = $request->input('qty', []);
            $updatedCount  = 0;

            foreach ($selectedItems as $productId) {
                $jumlahRestock = isset($qtys[$productId]) ? (int) $qtys[$productId] : 0;

                if ($jumlahRestock > 0) {
                    $product = Product::find($productId);

                    if ($product) {
                        $product->increment('stock', $jumlahRestock);

                        DB::table('restock_histories')->insert([
                            'user_id'     => auth()->id(),
                            'product_id'  => $product->id,
                            'supplier_id' => $request->supplier_id,
                            'jumlah'      => $jumlahRestock,
                            'harga_beli'  => $product->purchase_price,
                            'total_harga' => $jumlahRestock * $product->purchase_price,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);

                        $updatedCount++;
                    }
                }
            }

            DB::commit();

            if ($updatedCount > 0) {
                return redirect()->route('products.index')
                    ->with('success', "✨ Berhasil menambahkan stok untuk {$updatedCount} barang!");
            }

            return redirect()->route('products.index')
                ->with('error', 'Tidak ada jumlah stok yang ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses restock massal: ' . $e->getMessage());
        }
    }

    public function cetakTerlaris()
    {
        $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';
        
        $bulanIni = \Carbon\Carbon::now()->month;
        $tahunIni = \Carbon\Carbon::now()->year;

        $products = DB::table($namaTabelDetail)
            ->join('products', "{$namaTabelDetail}.product_id", '=', 'products.id')
            ->select(
                'products.name',
                'products.selling_price',
                DB::raw("SUM({$namaTabelDetail}.quantity) as total_qty"),
                DB::raw("SUM({$namaTabelDetail}.quantity * products.selling_price) as total_omset")
            )
            ->whereMonth("{$namaTabelDetail}.created_at", $bulanIni)
            ->whereYear("{$namaTabelDetail}.created_at", $tahunIni)
            ->groupBy('products.id', 'products.name', 'products.selling_price')
            ->orderByDesc('total_qty')
            ->get();

        return view('laporan.cetak-terlaris', compact('products'));
    }
}