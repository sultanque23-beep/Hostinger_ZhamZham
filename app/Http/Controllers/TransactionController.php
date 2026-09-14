<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Bundling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    // ==========================================
    // FUNGSI UNTUK MENAMPILKAN DAFTAR NOTA HARIAN
    // ==========================================
    public function index(Request $request)
    {
        $query = Transaction::query();
        $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';
        $hasBundlingColumn = Schema::hasColumn($namaTabelDetail, 'bundling_id');

        // 1. Filter Berdasarkan Jenis Pembelian (Paket vs Satuan)
        if ($request->filter === 'paket') {
            $query->whereExists(function ($q) use ($namaTabelDetail, $hasBundlingColumn) {
                $q->select(DB::raw(1))
                  ->from($namaTabelDetail)
                  ->whereColumn($namaTabelDetail . '.transaction_id', 'transactions.id');
                
                if ($hasBundlingColumn) {
                    $q->whereNotNull('bundling_id');
                } else {
                    $q->whereNull('product_id');
                }
            });
        } elseif ($request->filter === 'satuan') {
            $query->whereExists(function ($q) use ($namaTabelDetail, $hasBundlingColumn) {
                $q->select(DB::raw(1))
                  ->from($namaTabelDetail)
                  ->whereColumn($namaTabelDetail . '.transaction_id', 'transactions.id');
                
                if ($hasBundlingColumn) {
                    $q->whereNull('bundling_id')->whereNotNull('product_id');
                } else {
                    $q->whereNotNull('product_id');
                }
            });
        }

        // 2. Filter Berdasarkan Metode Pembayaran (Tunai / QRIS)
        if ($request->filled('payment_method')) {
            $query->where('payment_method', strtolower($request->payment_method));
        }

        $transactions = $query->latest()->get();

        return view('transactions.index', compact('transactions'));
    }

    // ==========================================
    // 1. FUNGSI UNTUK MEMBUKA HALAMAN KASIR
    // ==========================================
    public function create()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $bundlings = Bundling::with('details.product')->latest()->get();

        return view('transactions.create', compact('products', 'bundlings'));
    }

    // ==========================================
    // 2. FUNGSI UNTUK MENYIMPAN TRANSAKSI & POTONG STOK
    // ==========================================
    public function store(Request $request)
    {
        $request->validate([
            'pay_amount'     => 'required|numeric|min:0',
            'products'       => 'required|array|min:1',
            'payment_method' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';
            $hasBundlingColumn = Schema::hasColumn($namaTabelDetail, 'bundling_id');

            $calculatedTotalPrice = 0;
            $itemsToInsert = [];

            // Looping & kalkulasi ulang dari database (Mencegah manipulasi harga frontend)
            foreach ($request->products as $item) {
                $itemId   = $item['id'] ?? $item['product_id'] ?? null;
                $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                $itemType = $item['type'] ?? 'single';

                if (!$itemId || $quantity <= 0) {
                    continue;
                }

                if ($itemType === 'bundling') {
                    // JIKA ITEM ADALAH PAKET BUNDLING
                    $bundling = Bundling::with('details.product')->find($itemId);

                    if (!$bundling) {
                        throw new \Exception("Data paket bundling ID {$itemId} tidak ditemukan!");
                    }

                    // Validasi & potong stok komponen bahan bundling
                    foreach ($bundling->details as $detail) {
                        $subProduct = $detail->product;
                        if (!$subProduct) continue;

                        $totalNeededQty = $quantity * $detail->qty;

                        if ($subProduct->stock < $totalNeededQty) {
                            throw new \Exception("Stok '{$subProduct->name}' tidak cukup untuk paket {$bundling->name}! (Butuh: {$totalNeededQty}, Sisa: {$subProduct->stock})");
                        }

                        DB::table('products')
                            ->where('id', $subProduct->id)
                            ->decrement('stock', $totalNeededQty);
                    }

                    $bundlePrice = $bundling->bundle_price ?? $bundling->price ?? 0;
                    $calculatedTotalPrice += ($bundlePrice * $quantity);

                    $detailData = [
                        'quantity'   => $quantity,
                        'price'      => $bundlePrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($hasBundlingColumn) {
                        $detailData['bundling_id'] = $bundling->id;
                        $detailData['product_id']  = null;
                    } else {
                        $detailData['product_id']  = null;
                    }

                    $itemsToInsert[] = $detailData;

                } else {
                    // JIKA ITEM ADALAH PRODUK SATUAN
                    $product = DB::table('products')->where('id', $itemId)->first();

                    if (!$product) {
                        throw new \Exception("Produk ID {$itemId} tidak ditemukan!");
                    }

                    if ($product->stock < $quantity) {
                        throw new \Exception("Stok produk '{$product->name}' tidak mencukupi! (Sisa: {$product->stock})");
                    }

                    // Cek Harga Grosir vs Harga Reguler
                    $regularPrice   = $product->selling_price ?? $product->price ?? 0;
                    $wholesalePrice = $product->wholesale_price ?? $product->harga_grosir ?? $product->grosir_price ?? null;
                    $wholesaleMin   = $product->wholesale_min_qty ?? $product->min_grosir ?? $product->min_wholesale_qty ?? 0;

                    $finalUnitPrice = $regularPrice;
                    if ($wholesalePrice && $wholesaleMin > 0 && $quantity >= $wholesaleMin) {
                        $finalUnitPrice = $wholesalePrice;
                    }

                    // Potong stok secara atomis
                    DB::table('products')
                        ->where('id', $itemId)
                        ->decrement('stock', $quantity);

                    $calculatedTotalPrice += ($finalUnitPrice * $quantity);

                    $detailData = [
                        'product_id' => $product->id,
                        'quantity'   => $quantity,
                        'price'      => $finalUnitPrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($hasBundlingColumn) {
                        $detailData['bundling_id'] = null;
                    }

                    $itemsToInsert[] = $detailData;
                }
            }

            $payAmount = (int) $request->pay_amount;
            $totalPrice = $calculatedTotalPrice > 0 ? $calculatedTotalPrice : (int) $request->total_price;
            $changeAmount = $payAmount - $totalPrice;

            // FIX: Pengecekan multi-input & fallback string kosong
            $rawPaymentMethod = $request->input('payment_method') 
                             ?? $request->input('payment_type') 
                             ?? $request->input('metode_pembayaran');

            $paymentMethod = !empty(trim($rawPaymentMethod)) ? strtolower(trim($rawPaymentMethod)) : 'tunai';

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // Simpan data transaksi utama
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'total_price'    => $totalPrice,
                'pay_amount'     => $payAmount,
                'change_amount'  => $changeAmount,
                'payment_method' => $paymentMethod,
            ]);

            // Simpan detail transaksi
            foreach ($itemsToInsert as $insert) {
                $insert['transaction_id'] = $transaction->id;
                DB::table($namaTabelDetail)->insert($insert);
            }

            DB::commit();

            return redirect()->route('transactions.print', $transaction->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 3. DASHBOARD ANALITIK
    // ==========================================
    public function dashboard()
    {
        $totalPendapatan = Transaction::sum('total_price');
        $totalTransaksi = Transaction::count();
        $totalWarning = Product::where('stock', '<=', 10)->count(); 
        $recentTransactions = Transaction::orderBy('created_at', 'desc')->take(5)->get();

        $totalLabaBersih = 0;
        $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';

        $dataLaba = DB::table($namaTabelDetail)
            ->join('products', $namaTabelDetail . '.product_id', '=', 'products.id')
            ->select(
                $namaTabelDetail . '.quantity',
                $namaTabelDetail . '.price as harga_jual_nota',
                'products.purchase_price as harga_beli_modal'
            )
            ->get();

        foreach ($dataLaba as $item) {
            $hargaJual = $item->harga_jual_nota ?? 0;
            $hargaBeli = $item->harga_beli_modal ?? 0;
            $qty = $item->quantity ?? 0;

            $totalLabaBersih += ($hargaJual - $hargaBeli) * $qty;
        }

        return view('dashboard', compact('totalPendapatan', 'totalTransaksi', 'totalWarning', 'recentTransactions', 'totalLabaBersih'));
    }

    // ==========================================
    // 4. DETAIL RIWAYAT NOTA TRANSAKSI
    // ==========================================
    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $details = class_exists('\App\Models\TransactionDetail') 
            ? \App\Models\TransactionDetail::with('product')->where('transaction_id', $id)->get()
            : DB::table('details')->join('products', 'details.product_id', '=', 'products.id')->where('transaction_id', $id)->get();

        return view('transactions.show', compact('transaction', 'details'));
    }

    // ==========================================
    // 5. FUNGSI UNTUK MERESET / MENGOSONGKAN NOTA
    // ==========================================
    public function reset()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';
        DB::table($namaTabelDetail)->truncate();
        Transaction::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return redirect()->route('products.index')->with('success', 'Semua riwayat nota harian BERHASIL dikosongkan total dari nol!');
    }

    // ==========================================
    // 6. FUNGSI UNTUK CETAK SEMUA NOTA SEKALIGUS
    // ==========================================
    public function printAll()
    {
        $transactions = Transaction::orderBy('created_at', 'desc')->get();

        if ($transactions->isEmpty()) {
            return redirect()->back()->with('error', 'Kosong! Belum ada riwayat nota harian yang bisa dicetak.');
        }

        return view('transactions.print_all', compact('transactions'));
    }

    // ==========================================
    // 7. FUNGSI UNTUK CETAK SATU NOTA TRANSAKSI
    // ==========================================
    public function print($id)
    {
        $transaction = Transaction::findOrFail($id);
        $namaTabelDetail = Schema::hasTable('transaction_details') ? 'transaction_details' : 'details';

        $hasBundlingColumn = Schema::hasColumn($namaTabelDetail, 'bundling_id');

        $query = DB::table($namaTabelDetail)
            ->leftJoin('products', $namaTabelDetail . '.product_id', '=', 'products.id');

        if ($hasBundlingColumn) {
            $bundlingColumnName = Schema::hasColumn('bundlings', 'name') ? 'name' : 'bundle_name';

            $query->leftJoin('bundlings', $namaTabelDetail . '.bundling_id', '=', 'bundlings.id')
                  ->select(
                      $namaTabelDetail . '.*',
                      DB::raw("COALESCE(products.name, CONCAT('[PAKET] ', bundlings.{$bundlingColumnName})) as item_name")
                  );
        } else {
            $query->select(
                $namaTabelDetail . '.*',
                'products.name as item_name'
            );
        }

        $details = $query->where('transaction_id', $id)->get();

        return view('transactions.print', compact('transaction', 'details'));
    }
}