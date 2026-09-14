@extends('layouts.app')

@section('title', 'Dashboard - Grosir ZhamZham')

@section('content')
<div class="space-y-8">
    
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Utama</h2>
        <p class="text-xs font-semibold text-slate-400 mt-0.5">Pantau pendapatan harian dan status inventori toko Anda</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between group hover:border-emerald-300 transition-all duration-200">
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block truncate">Total Omset Hari Ini</span>
                <h3 class="text-xl font-extrabold text-slate-900 font-money truncate mt-0.5">
                    Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="bg-emerald-500 text-white p-3 rounded-xl shadow-md shadow-emerald-100 group-hover:scale-105 transition-transform duration-200 ml-3 shrink-0">
                <i class="fa-solid fa-money-bill-wave text-base"></i>
            </div>
        </div>

        <div class="bg-emerald-50/60 p-5 rounded-2xl border border-emerald-200 shadow-sm flex items-center justify-between group hover:border-emerald-400 transition-all duration-200">
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block truncate">Laba Bersih (Untung)</span>
                <h3 class="text-xl font-extrabold text-emerald-700 font-money truncate mt-0.5">
                    Rp {{ number_format($totalLabaBersih ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-3 rounded-xl shadow-md shadow-emerald-200 group-hover:scale-105 transition-transform duration-200 ml-3 shrink-0">
                <i class="fa-solid fa-coins text-base"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between group hover:border-teal-300 transition-all duration-200">
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block truncate">Transaksi Selesai</span>
                <h3 class="text-xl font-extrabold text-slate-900 font-money truncate mt-0.5">
                    {{ $totalTransaksi ?? 0 }} <span class="text-xs font-bold text-slate-400 font-sans">Nota</span>
                </h3>
            </div>
            <div class="bg-teal-500 text-white p-3 rounded-xl shadow-md shadow-teal-100 group-hover:scale-105 transition-transform duration-200 ml-3 shrink-0">
                <i class="fa-solid fa-square-poll-vertical text-base"></i>
            </div>
        </div>

        <div class="bg-rose-50/50 p-5 rounded-2xl border border-rose-100 shadow-sm flex items-center justify-between group hover:border-rose-200 transition-all duration-200">
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider block truncate">Early Warning Sistem</span>
                <h3 class="text-xl font-extrabold text-rose-700 truncate mt-0.5">
                    {{ $totalWarning ?? 0 }} <span class="text-xs font-semibold text-rose-500 font-sans">Barang</span>
                </h3>
                <div class="flex gap-1 pt-1">
                    <span class="px-1.5 py-0.2 text-[8px] font-bold rounded bg-rose-100 text-rose-700 border border-rose-200">expired</span>
                    <span class="px-1.5 py-0.2 text-[8px] font-bold rounded bg-amber-100 text-amber-700 border border-amber-200">low_stock</span>
                </div>
            </div>
            <div class="bg-rose-500 text-white p-3 rounded-xl shadow-md shadow-rose-100 group-hover:scale-105 transition-transform duration-200 ml-3 shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-base"></i>
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Laporan Transaksi Harian</h4>
                <p class="text-[11px] font-medium text-slate-400">Daftar penjualan terbaru yang masuk ke meja kasir</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition duration-150 flex items-center gap-1">
                Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Tanggal & Waktu</th>
                        <th class="p-4">Nomor Nota (Invoice)</th>
                        <th class="p-4 text-right pr-8">Total Pendapatan</th>
                        <th class="p-4 w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    @forelse($recentTransactions ?? [] as $key => $transaction)
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="p-4 text-center text-slate-400 font-bold">{{ $key + 1 }}</td>
                        <td class="p-4">{{ $transaction->created_at->format('d M Y, H:i') }} WIB</td>
                        <td class="p-4">
                            <span class="bg-slate-100 text-slate-800 px-2.5 py-1 rounded-md font-money font-bold border border-slate-200">
                                {{ $transaction->invoice_number }}
                            </span>
                        </td>
                        <td class="p-4 text-right pr-8 font-money font-bold text-emerald-600">
                            Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('transactions.show', $transaction->id) }}" target="_blank" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg text-[11px] shadow-sm transition duration-150">
                                <i class="fa-solid fa-print"></i> Aksi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400 font-medium bg-slate-50/30">
                            <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300 d-block"></i><br>
                            Belum ada transaksi hari ini yang terekam di database kasir.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection