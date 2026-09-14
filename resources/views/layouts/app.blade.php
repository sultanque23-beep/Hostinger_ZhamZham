<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Grosir ZhamZham POS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-money { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800">

    <!-- Container Utama: Flex-Col di HP, Flex-Row di Desktop (md:) -->
    <div class="flex flex-col md:flex-row h-screen overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="w-full md:w-64 bg-white border-b md:border-b-0 md:border-r border-slate-200 flex flex-col justify-between shrink-0">
            <div>
                <div class="p-4 md:p-6 border-b border-slate-100 flex items-center gap-3">
                    <div class="bg-emerald-600 text-white p-2.5 rounded-xl shadow-md shadow-emerald-200">
                        <i class="fa-solid fa-shop text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-slate-900 tracking-tight leading-none text-base">Grosir ZhamZham</h1>
                        <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Sembako System</span>
                    </div>
                </div>

                <!-- Navigasi: Scroll Horizontal di HP, List Vertikal di PC (md:) -->
                <nav class="p-2 md:p-4 flex md:flex-col gap-1.5 overflow-x-auto md:overflow-x-visible">
                    
                    <!-- MENU KHUSUS OWNER -->
                    @if(Auth::check() && Auth::user()->role === 'owner')
                        <a href="{{ url('/dashboard') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::is('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-base {{ Request::is('dashboard') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                            Dashboard
                        </a>
                    @endif

                    <!-- MENU UTAMA (BISA DIAKSES KASIR & OWNER) -->
                    <a href="{{ route('transactions.create') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::is('transactions/create') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i class="fa-solid fa-cash-register w-5 text-center text-base {{ Request::is('transactions/create') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        Meja Kasir
                    </a>

                    <!-- MENU KHUSUS OWNER (STOK, LAPORAN, BUNDLING, RESTOCK) -->
                    @if(Auth::check() && Auth::user()->role === 'owner')
                        <a href="{{ route('products.index') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::is('products*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fa-solid fa-boxes-stacked w-5 text-center text-base {{ Request::is('products*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                            Stok Barang
                        </a>

                        <a href="{{ route('transactions.index') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::is('transactions') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fa-solid fa-receipt w-5 text-center text-base {{ Request::is('transactions') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                            Laporan Nota
                        </a>

                        <a href="{{ route('bundling.index') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::is('bundling*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fa-solid fa-boxes-packing w-5 text-center text-base {{ Request::is('bundling*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                            Paket Bundling
                        </a>

                        <a href="{{ route('owner.restock.history') }}" class="whitespace-nowrap flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ Request::routeIs('owner.restock.history') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base {{ Request::routeIs('owner.restock.history') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                            Riwayat Restock
                        </a>
                    @endif

                </nav>
            </div>

            <!-- BAGIAN BAWAH SIDEBAR (LOGOUT & RESET NOTA) -->
            <div class="p-2 md:p-4 border-t border-slate-100 flex md:flex-col gap-2">
                <!-- TOMBOL LOGOUT -->
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
                            class="w-full flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 text-xs rounded-xl transition duration-150 border border-slate-200 cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-right-from-bracket text-slate-500"></i> Keluar / Logout
                    </button>
                </form>

                <!-- TOMBOL RESET NOTA (KHUSUS OWNER) -->
                @if(Auth::check() && Auth::user()->role === 'owner')
                    <form action="{{ route('transactions.reset') }}" method="POST" onsubmit="return confirm('⚠️ HAPUS RIWAYAT? Tindakan ini akan mengosongkan semua nota dan tidak bisa dibatalkan!');" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 border border-rose-200 hover:bg-rose-50 text-rose-600 font-bold py-2.5 px-4 text-xs rounded-xl transition duration-150 cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-triangle-exclamation animate-pulse"></i> Reset Nota Harian
                        </button>
                    </form>
                @endif
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- HEADER -->
            <header class="h-16 md:h-20 bg-white border-b border-slate-200 px-4 md:px-8 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-400">
                        @if(Auth::check() && Auth::user()->role === 'owner')
                            Halo pak,
                        @else
                            Selamat Bekerja,
                        @endif
                    </span>
                    <span class="text-sm font-bold text-slate-700">
                        {{ Auth::user()->name ?? 'User' }} 👋
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative bg-slate-100 text-slate-600 w-10 h-10 rounded-full flex items-center justify-center font-bold border border-slate-200 shadow-sm uppercase">
                        {{ strtoupper(substr(Auth::user()->name ?? 'K', 0, 1)) }}
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-800 leading-none">
                            {{ Auth::check() && Auth::user()->role === 'owner' ? 'Owner Utama' : 'Kasir Utama' }}
                        </p>
                        <span class="text-[10px] font-semibold text-slate-400">
                            {{ ucfirst(Auth::user()->role ?? 'kasir') }} Session
                        </span>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>