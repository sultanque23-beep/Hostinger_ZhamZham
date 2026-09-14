@extends('layouts.app')

@section('title', 'Laporan Transaksi - Grosir ZhamZham')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER KONTEN -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Nota Penjualan</h2>
            <p class="text-xs font-semibold text-slate-400 mt-0.5">Halaman rekaman audit laporan transaksi harian Grosir ZhamZham</p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('transactions.printAll') }}" target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 text-sm rounded-xl shadow-md shadow-emerald-100 transition duration-150">
                <i class="fa-solid fa-print text-xs"></i> Cetak Semua Nota Hari Ini
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>✨ {{ session('success') }}</span>
        </div>
    @endif

    <!-- FILTER BARIS / TOMBOL FILTER -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <div class="flex flex-wrap items-center gap-4">
                
                <!-- GROUP 1: FILTER JENIS NOTA -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider pr-1">Filter Jenis:</span>
                    
                    <!-- All Nota -->
                    <a href="{{ route('transactions.index', array_merge(request()->except('filter'))) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ !request('filter') ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        <i class="fa-solid fa-list-ul"></i> Semua Nota
                    </a>

                    <!-- Satuan -->
                    <a href="{{ route('transactions.index', array_merge(request()->query(), ['filter' => 'satuan'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ request('filter') === 'satuan' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100' }}">
                        <i class="fa-solid fa-box"></i> Beli Satuan
                    </a>

                    <!-- Paket -->
                    <a href="{{ route('transactions.index', array_merge(request()->query(), ['filter' => 'paket'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ request('filter') === 'paket' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100' }}">
                        <i class="fa-solid fa-boxes-packing"></i> Beli Paket Bundling
                    </a>
                </div>

                <div class="hidden lg:block h-6 w-px bg-slate-200"></div>

                <!-- GROUP 2: FILTER METODE PEMBAYARAN -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider pr-1">Pembayaran:</span>

                    <!-- Semua Pembayaran -->
                    <a href="{{ route('transactions.index', array_merge(request()->except('payment_method'))) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ !request('payment_method') ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        Semua
                    </a>

                    <!-- Tunai -->
                    <a href="{{ route('transactions.index', array_merge(request()->query(), ['payment_method' => 'tunai'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ request('payment_method') === 'tunai' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100' }}">
                        <i class="fa-solid fa-money-bill-wave"></i> Tunai
                    </a>

                    <!-- QRIS -->
                    <a href="{{ route('transactions.index', array_merge(request()->query(), ['payment_method' => 'qris'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-150 {{ request('payment_method') === 'qris' ? 'bg-sky-600 text-white shadow-sm' : 'bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-100' }}">
                        <i class="fa-solid fa-qrcode"></i> QRIS
                    </a>
                </div>

            </div>

            <div class="text-xs text-slate-400 font-semibold px-2 whitespace-nowrap">
                Menampilkan: <span class="text-slate-700 font-bold">{{ $transactions->count() }}</span> Nota
            </div>

        </div>
    </div>

    <!-- TABEL TRANSAKSI -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4 w-44 text-center">Tanggal & Waktu</th>
                        <th class="p-4 text-center">Nomor Nota (Invoice)</th>
                        <th class="p-4 w-32 text-center">Metode Bayar</th>
                        <th class="p-4 text-right pr-12">Total Pendapatan</th>
                        <th class="p-4 w-32 text-center">Aksi Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    @forelse($transactions as $key => $transaction)
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="p-4 text-center text-slate-400 font-bold">{{ $key + 1 }}</td>
                        <td class="p-4 text-center text-slate-500">{{ $transaction->created_at->format('d F Y, H:i') }} WIB</td>
                        <td class="p-4 text-center">
                            <span class="bg-slate-100 text-slate-800 px-3 py-1 rounded-lg font-money font-bold text-xs border border-slate-200">
                                {{ $transaction->invoice_number }}
                            </span>
                        </td>
                        
                        <!-- COLOM METODE PEMBAYARAN -->
                        <td class="p-4 text-center">
                            @if(strtolower($transaction->payment_method ?? 'tunai') === 'qris')
                                <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-1 rounded-lg font-extrabold text-[11px]">
                                    <i class="fa-solid fa-qrcode text-sky-600"></i> QRIS
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg font-extrabold text-[11px]">
                                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Tunai
                                </span>
                            @endif
                        </td>

                        <td class="p-4 text-right pr-12 font-money font-bold text-emerald-600 text-sm">
                            Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1.5 px-3 rounded-xl shadow-sm transition duration-150 text-[11px]">
                                <i class="fa-solid fa-receipt text-slate-400"></i> Cetak Struk
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-16 text-center text-slate-400 font-medium bg-slate-50/30">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada riwayat nota transaksi harian yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection