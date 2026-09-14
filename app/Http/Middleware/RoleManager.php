<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleManager
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Jika belum login, biarkan middleware auth bawaan Laravel yang bekerja
        if (!Auth::check()) {
            return $next($request);
        }

        $userRole = Auth::user()->role;

        // Owner adalah super admin, boleh akses apa saja (termasuk area kasir)
        if ($userRole === 'owner') {
            return $next($request);
        }

        // Jika kasir mencoba mengakses area owner, langsung tolak dengan kode 403 (Forbidden)
        if ($role === 'owner' && $userRole !== 'owner') {
            abort(403, 'Akses Ditolak! Halaman ini hanya untuk Owner/Kepala Toko.');
        }

        return $next($request);
    }
}