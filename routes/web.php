<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ExpiredBatchController;
use App\Http\Controllers\BundlingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Supplier\RestockController;
use App\Http\Controllers\RestockHistoryController;
use App\Http\Controllers\SupplierController;


// 1. ROUTE AUTHENTICATION BREEZE/FORTIFY
require __DIR__.'/auth.php';

// =========================================================================
// JALUR TERPROTEKSI LOGIN & PERSETUJUAN ('auth', 'approved')
// =========================================================================
Route::middleware(['auth', 'verified', 'approved'])->group(function () {

    // A. JALUR UMUM (Dashboard & Profil)
    Route::get('/', [TransactionController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // B. JALUR MEJA KASIR (Role: kasir & owner)
    Route::middleware(['role:kasir|owner'])->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{id}/print', [TransactionController::class, 'print'])->name('transactions.print');
    });


   // C. JALUR KELOLA STOK & BARANG (Role: gudang & owner)
    Route::middleware(['role:gudang|owner'])->group(function () {
        
        // PENTING: Route restock-massal harus diletakkan SEBELUM Route::resource 'products'
        // agar tidak terbaca sebagai parameter {id} oleh fungsi show()
        Route::post('/products/restock-massal', [ProductController::class, 'restockFromSupplier'])->name('products.restock-massal');
        
        Route::resource('products', ProductController::class);

        // KELOLA PAKET BUNDLING (LENGKAP: CRUD)
        Route::get('/bundling', [BundlingController::class, 'index'])->name('bundling.index');
        Route::get('/bundling/create', [BundlingController::class, 'create'])->name('bundling.create');
        Route::post('/bundling/store', [BundlingController::class, 'store'])->name('bundling.store');
        Route::get('/bundling/{id}/edit', [BundlingController::class, 'edit'])->name('bundling.edit');
        Route::put('/bundling/{id}', [BundlingController::class, 'update'])->name('bundling.update');
        Route::delete('/bundling/{id}', [BundlingController::class, 'destroy'])->name('bundling.destroy');
    });


    // D. JALUR KHUSUS OWNER (Role: owner)
    Route::middleware(['role:owner'])->group(function () {
        
        // ROUTE MASTER DATA SUPPLIER
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Fitur Approval User / Pegawai Baru
        Route::get('/owner/users', [UserController::class, 'index'])->name('owner.users.index');
        Route::patch('/owner/users/{user}/approve', [UserController::class, 'approve'])->name('owner.users.approve');
        Route::delete('/owner/users/{user}', [UserController::class, 'destroy'])->name('owner.users.destroy');

        // Riwayat Barang Masuk dari Supplier
        Route::get('/riwayat-restock', [RestockHistoryController::class, 'index'])->name('owner.restock.history');
        // Riwayat Barang Masuk dari Supplier
        Route::get('/riwayat-restock', [RestockHistoryController::class, 'index'])->name('owner.restock.history');
        Route::delete('/riwayat-restock/reset', [RestockHistoryController::class, 'reset'])->name('owner.restock.history.reset');

        // Laporan & Cetak Laporan
        Route::get('/laporan-transaksi', [TransactionController::class, 'index'])->name('laporan.index');
        Route::get('/laporan-transaksi/cetak-semua', [TransactionController::class, 'printAll'])->name('transactions.printAll');
        Route::get('/laporan-transaksi/cetak/{transaction}', [TransactionController::class, 'show'])->name('laporan.cetak');
        Route::post('/transactions/reset', [TransactionController::class, 'reset'])->name('transactions.reset');
        Route::get('/products/cetak-terlaris', [ProductController::class, 'cetakTerlaris'])->name('products.cetak-terlaris');

        // Riwayat Detail Transaksi
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });


    // E. JALUR KHUSUS SUPPLIER (Role: supplier)
    Route::middleware(['role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/stok', [RestockController::class, 'index'])->name('stok.index');
        Route::post('/stok/tambah/{id}', [RestockController::class, 'addStock'])->name('stok.add');
    });


    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        
        // Route logout langsung via URL browser (GET /logout)
        Route::get('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login');
        })->name('logout.get');
    });
});

// JALUR TES TERISOLASI
Route::get('/tes-potong-stok', function() {
    $product = \DB::table('products')->first();
    
    if (!$product) {
        return "Gagal: Tidak ada produk sama sekali di database Anda!";
    }

    $stokAwal = $product->stock;

    \DB::table('products')->where('id', $product->id)->update([
        'stock' => $stokAwal - 1
    ]);

    $productBaru = \DB::table('products')->where('id', $product->id)->first();

    return "<h3>=== TES POTONG STOK SELESAI ===</h3>" .
           "Nama Produk: " . $product->name . "<br>" .
           "Stok Awal di DB: " . $stokAwal . "<br>" .
           "Stok Setelah Dipotong Kode: " . $productBaru->stock . "<br><br>";
});