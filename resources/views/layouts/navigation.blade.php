<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php
                        $u = Auth::user();
                        
                        // Pengecekan multi-kondisi (Properti role, Spatie hasRole, dan email owner)
                        $isOwner = ($u->role === 'owner') || ($u->hasRole('owner') ?? false) || in_array($u->email, ['owner@zhamzham.com', 'sultanque23@gmail.com']);
                        $isKasir = $isOwner || ($u->role === 'kasir') || ($u->hasRole('kasir') ?? false);
                        $isGudang = $isOwner || ($u->role === 'gudang') || ($u->hasRole('gudang') ?? false);
                    @endphp

                    <!-- Dashboard (Bisa diakses semua role) -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Transaksi Kasir (Hanya Kasir & Owner) -->
                    @if($isKasir && !$isGudang)
                        <x-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')">
                            {{ __('Transaksi Kasir') }}
                        </x-nav-link>
                    @elseif($isOwner)
                        <x-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')">
                            {{ __('Transaksi Kasir') }}
                        </x-nav-link>
                    @endif

                    <!-- Stok & Bundling (Hanya Gudang & Owner) -->
                    @if($isGudang)
                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                            {{ __('Stok Produk') }}
                        </x-nav-link>

                        <x-nav-link :href="route('bundling.index')" :active="request()->routeIs('bundling.*')">
                            {{ __('Paket Bundling') }}
                        </x-nav-link>
                    @endif

                    <!-- Menu Khusus Owner -->
                    @if($isOwner)
                        <x-nav-link :href="route('owner.users.index')" :active="request()->routeIs('owner.users.*')">
                            {{ __('Persetujuan Pegawai') }}
                        </x-nav-link>

                        <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*') || request()->routeIs('transactions.index')">
                            {{ __('Laporan Transaksi') }}
                        </x-nav-link>

                        <x-nav-link :href="route('owner.restock.history')" :active="request()->routeIs('owner.restock.history')">
                            {{ __('Riwayat Restock') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Profile & Tombol Logout -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <div class="text-sm font-medium text-gray-700">
                    {{ Auth::user()->name }} 
                    <span class="text-xs text-emerald-600 font-bold">
                        ({{ strtoupper(Auth::user()->role ?? (Auth::user()->getRoleNames()->first() ?? 'USER')) }})
                    </span>
                </div>

                <!-- FORM LOGOUT AKSI POST -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition cursor-pointer">
                        Log Out
                    </button>
                </form>
            </div>

            <!-- Hamburger Button (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if($isKasir && !$isGudang)
                <x-responsive-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')">
                    {{ __('Transaksi Kasir') }}
                </x-responsive-nav-link>
            @elseif($isOwner)
                <x-responsive-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')">
                    {{ __('Transaksi Kasir') }}
                </x-responsive-nav-link>
            @endif

            @if($isGudang)
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    {{ __('Stok Produk') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('bundling.index')" :active="request()->routeIs('bundling.*')">
                    {{ __('Paket Bundling') }}
                </x-responsive-nav-link>
            @endif

            @if($isOwner)
                <x-responsive-nav-link :href="route('owner.users.index')" :active="request()->routeIs('owner.users.*')">
                    {{ __('Persetujuan Pegawai') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                    {{ __('Laporan Transaksi') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('owner.restock.history')" :active="request()->routeIs('owner.restock.history')">
                    {{ __('Riwayat Restock') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 px-4">
            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-gray-500 mb-2">{{ Auth::user()->email }}</div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left font-bold text-red-600 py-1">Log Out</button>
            </form>
        </div>
    </div>
</nav>