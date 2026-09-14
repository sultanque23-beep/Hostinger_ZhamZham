<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestockHistoryController extends Controller
{
    public function index()
    {
        $histories = DB::table('restock_histories')
            ->leftJoin('products', 'restock_histories.product_id', '=', 'products.id')
            ->leftJoin('suppliers', 'restock_histories.supplier_id', '=', 'suppliers.id')
            ->select(
                'restock_histories.*',
                'products.name as nama_barang',
                'suppliers.nama_supplier'
            )
            ->latest('restock_histories.created_at')
            ->paginate(10);

        return view('owner.restock_history', compact('histories'));
    }

    public function reset()
    {
        try {
            DB::table('restock_histories')->truncate();
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('restock_histories')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return redirect()->back()->with('success', 'Semua riwayat restock berhasil dikosongkan!');
    }
}