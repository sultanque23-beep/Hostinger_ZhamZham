<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('suppliers')->orderBy('id', 'desc')->get();
        return view('owner.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
        ]);

        DB::table('suppliers')->insert([
            'nama_supplier' => $request->nama_supplier,
            'no_hp'         => $request->no_hp,
            'alamat'        => $request->alamat,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        DB::table('suppliers')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Supplier berhasil dihapus!');
    }
}