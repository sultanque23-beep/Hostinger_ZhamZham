@extends('layouts.app')

@section('title', 'Dashboard Owner - Grosir ZhamZham')

@section('content')
<div class="space-y-8">
    
    <!-- HEADER DASHBOARD -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">👑 Dashboard Kepala Toko</h2>
            <p class="text-xs font-semibold text-slate-400 mt-0.5">Pantau ringkasan omzet, laba bersih, dan kontrol operasional Grosir ZhamZham</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                Sistem Monitoring Aktif
            </span>
        </div>
    </div>

    <!-- CARDS INFORMASI UTAMA -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- CARD 1: OMZET -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl shadow-sm">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Omzet Penjualan</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- CARD 2: LABA BERSIH (ESTIMASI) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 text-xl shadow-sm">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Estimasi Laba Bersih</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5">Rp {{ number_format($totalLabaBersih, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- CARD 3: TOTAL PRODUK -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl shadow-sm">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Jenis Produk</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $totalProducts }} Item</span>
            </div>
        </div>

        <!-- CARD 4: PERINGATAN STOK TIPIS -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl {{ $lowStockProducts->count() > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-600' }} flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Stok Sembako Kritis</span>
                <span class="text-xl font-extrabold {{ $lowStockProducts->count() > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-0.5">
                    {{ $lowStockProducts->count() }} Produk
                </span>
            </div>
        </div>

    </div>

    <!-- GRID LAYOUT UTAMA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- SISI KIRI: STATS & TRANSAKSI -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- STOK KRITIS ALERT -->
            @if($lowStockProducts->count() > 0)
                <div class="bg-white rounded-2xl border border-rose-200 shadow-sm overflow-hidden">
                    <div class="p-4 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                        <span class="font-bold text-rose-800 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation animate-bounce"></i> 🚨 PERINGATAN: Sembako Hampir Habis!
                        </span>
                        <!-- PERBAIKAN 1: Diubah ke owner.products.index -->
                        <a href="{{ route('owner.products.index') }}" class="text-[10px] font-black uppercase text-rose-600 hover:underline">Selesaikan Restock &rarr;</a>
                    </div>
                    <div class="p-4 divide-y divide-slate-100">
                        @foreach($lowStockProducts as $product)
                            <div class="flex items-center justify-between py-2.5 text-xs">
                                <span class="font-semibold text-slate-800">{{ $product->name }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-slate-400 font-mono">Kode: {{ $product->product_code }}</span>
                                    <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-md font-bold">Sisa {{ $product->stock }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 5 TRANSAKSI TERAKHIR KASIR -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-slate-800 text-xs flex justify-between items-center">
                    <span>⚡ Aktivitas Transaksi Kasir Terakhir</span>
                    <!-- PERBAIKAN 2: Diubah ke owner.transactions.index -->
                    <a href="{{ route('owner.transactions.index') }}" class="text-[10px] font-black text-emerald-600 hover:underline uppercase">Lihat Semua Riwayat</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 text-slate-500 font-bold uppercase tracking-wider">
                                <th class="p-4">Waktu</th>
                                <th class="p-4 text-center">ID Transaksi</th>
                                <th class="p-4 text-right">Total Belanja</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 text-slate-500">{{ $tx->created_at->format('d M Y, H:i') }} WIB</td>
                                    <td class="p-4 text-center font-bold text-slate-800 font-mono">#TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="p-4 text-right font-money font-bold text-slate-900">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center">
                                        <span class="bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full text-[10px] font-bold">Selesai</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">Belum ada aktivitas transaksi hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- SISI KANAN: SHORTCUT NAVIGASI OWNER -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-2">
                    🛠️ Kendali Cepat Kepala Toko
                </h3>
                <div class="grid grid-cols-1 gap-3">
                    <!-- PERBAIKAN 3: Diubah ke owner.products.index -->
                    <a href="{{ route('owner.products.index') }}" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition font-bold text-xs text-slate-800 border border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-boxes-stacked"></i></span>
                        Atur Stok Sembako
                    </a>
                    
                    <!-- PERBAIKAN 4: Diubah ke owner.bundling.index jika rute bundling juga diproteksi owner -->
                    <a href="{{ route('owner.bundling.index') }}" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition font-bold text-xs text-slate-800 border border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center"><i class="fa-solid fa-gift"></i></span>
                        Kelola Paket Bundling
                    </a>
                    
                    <a href="{{ route('owner.laporan') }}" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition font-bold text-xs text-slate-800 border border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                        Cetak Laporan Penjualan
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection