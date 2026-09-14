<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction; 
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<', 10)->get();

        
        $transactions = Transaction::all(); 
        
        $totalOmzet = $transactions->sum('total_price');
        
       
        $totalLabaBersih = 0; 
        
       
        $recentTransactions = Transaction::latest()->take(5)->get();

        return view('owner.dashboard', compact(
            'totalProducts',
            'lowStockProducts',
            'totalOmzet',
            'totalLabaBersih',
            'recentTransactions'
        ));
    }

    public function laporan()
    {
       
        return view('owner.laporan');
    }
}