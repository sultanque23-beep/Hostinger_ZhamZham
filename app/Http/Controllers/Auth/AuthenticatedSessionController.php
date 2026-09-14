<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $email = strtolower(trim($request->input('email')));

        // 1. Cek keberadaan user di database
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Jika user tidak ditemukan di DB
            throw ValidationException::withMessages([
                'email' => 'User tidak dikenal. Silakan hubungi Owner untuk dibuatkan akun.',
            ]);
        }

        // 2. Jika user ada, validasi password lewat mekanisme bawaan Laravel
        $request->authenticate();

        // 3. Fallback jika kolom role di DB masih kosong/null
        $roleName = $user->role;
        if (empty($roleName)) {
            $ownerEmails = ['owner@zhamzham.com', 'sultanque23@gmail.com'];
            $roleName = in_array($user->email, $ownerEmails) ? 'owner' : 'kasir';
            
            $user->update(['role' => $roleName]);
        }

        // 4. Sinkronisasi Spatie Role jika package terpasang
        if (class_exists(\Spatie\Permission\Models\Role::class) && !empty($roleName)) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName]);
            $user->syncRoles([$roleName]);
        }

        $request->session()->regenerate();

        // 5. Redirection berdasarkan Role
        if ($user->role === 'supplier' || $user->hasRole('supplier')) {
            return redirect()->route('supplier.stok.index');
        }

        if ($user->role === 'owner' || $user->hasRole('owner')) {
            return redirect()->route('dashboard');
        }

        if ($user->role === 'gudang' || $user->hasRole('gudang')) {
            return redirect()->route('products.index');
        }

        return redirect()->route('transactions.create');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}