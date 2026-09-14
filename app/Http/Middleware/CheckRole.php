<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // 1. Jika belum login sama sekali, lempar ke halaman login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Ambil data user yang sedang login
        $user = Auth::user();

        // 3. Jika role sesuai, izinkan lewat
        if ($user->role === $role) {
            return $next($request);
        }

        // 4. Pengalihan jika role TIDAK sesuai:
        // Jika Kasir mencoba masuk ke area Owner, kembalikan ke Kasir
        if ($user->role === 'kasir') {
            return redirect()->route('transactions.create')->with('error', 'Anda tidak memiliki akses ke halaman ini!');
        }

        // Jika Owner mencoba masuk ke area lain / default, arahkan ke Dashboard
        return redirect()->route('dashboard');
    }
}