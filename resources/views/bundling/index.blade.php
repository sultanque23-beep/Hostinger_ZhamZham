@extends('layouts.app') {{-- Menyesuaikan file layout utama kamu --}}

@section('title', 'Daftar Paket Bundling')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Paket Bundling</h2>
            <p class="text-sm text-slate-500">Daftar strategi promosi bundling aktif di Grosir ZhamZham</p>
        </div>
        <a href="{{ route('bundling.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition duration-150 shadow-md shadow-emerald-100 flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Buat Paket Baru
        </a>
    </div>

    {{-- Form Pencarian & Tombol Filter --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Form Pencarian --}}
        <form action="{{ route('bundling.index') }}" method="GET" class="flex items-center gap-2 max-w-md w-full">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode paket..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition">
            </div>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                Cari
            </button>
            @if(request('search') || request('filter'))
                <a href="{{ route('bundling.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold py-2.5 px-4 rounded-xl text-sm transition whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tombol Filter Renceng & Dus --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Filter:</span>
            
            <!-- Filter Semua -->
            <a href="{{ route('bundling.index', array_merge(request()->except('filter'), [])) }}" 
               class="px-3.5 py-2 text-xs font-semibold rounded-xl transition border {{ !request('filter') ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <i class="fa-solid fa-border-all mr-1"></i> Semua
            </a>

            <!-- Filter 1 Renceng (6-15 pcs) -->
            <a href="{{ route('bundling.index', array_merge(request()->except('filter'), ['filter' => 'renceng'])) }}" 
               class="px-3.5 py-2 text-xs font-semibold rounded-xl transition border {{ request('filter') === 'renceng' ? 'bg-amber-500 text-white border-amber-500' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                <i class="fa-solid fa-cubes-stacked mr-1"></i> 1 Renceng (6–15 pcs)
            </a>

            <!-- Filter 1 Dus (24-48 pcs) -->
            <a href="{{ route('bundling.index', array_merge(request()->except('filter'), ['filter' => 'dus'])) }}" 
               class="px-3.5 py-2 text-xs font-semibold rounded-xl transition border {{ request('filter') === 'dus' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100' }}">
                <i class="fa-solid fa-box-open mr-1"></i> 1 Dus (24–48 pcs)
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Notifikasi Error --}}
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabel Daftar Bundling -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Kode Paket</th>
                        <th class="px-6 py-4">Nama Paket Promo</th>
                        <th class="px-6 py-4 text-center">Tipe / Satuan</th>
                        <th class="px-6 py-4">Isi Komponen Produk</th>
                        <th class="px-6 py-4">Harga Paket</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-700">
                    @forelse($bundlings as $bundle)
                        @php
                            // Hitung total kuantitas dari komponen produk di dalam paket
                            $totalQty = $bundle->details->sum('qty');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono text-emerald-600 text-xs">{{ $bundle->bundle_code }}</td>
                            <td class="px-6 py-4 text-slate-900">{{ $bundle->name }}</td>
                            
                            {{-- Badge Tipe Satuan Automatis --}}
                            <td class="px-6 py-4 text-center">
                                @if($totalQty >= 6 && $totalQty <= 15)
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-lg text-xs font-bold">
                                        <i class="fa-solid fa-cubes-stacked text-amber-500"></i> Renceng ({{ $totalQty }} pcs)
                                    </span>
                                @elseif($totalQty >= 24 && $totalQty <= 48)
                                    <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-lg text-xs font-bold">
                                        <i class="fa-solid fa-box-open text-indigo-500"></i> Dus ({{ $totalQty }} pcs)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-lg text-xs font-bold">
                                        <i class="fa-solid fa-layer-group text-slate-400"></i> Custom ({{ $totalQty }} pcs)
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <ul class="list-disc list-inside space-y-1 text-xs text-slate-500 font-normal">
                                    @foreach($bundle->details as $detail)
                                        <li>{{ $detail->product->name ?? 'Produk Tidak Ditemukan' }} <span class="font-bold text-slate-700">({{ $detail->qty }} pcs)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 font-money text-slate-900">Rp {{ number_format($bundle->bundle_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('bundling.edit', $bundle->id) }}" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-lg transition" title="Edit Paket">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('bundling.destroy', $bundle->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket bundling ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus Paket">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-normal">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                @if(request('search') || request('filter'))
                                    Data paket bundling tidak ditemukan sesuai kata kunci atau filter yang dipilih.
                                @else
                                    Belum ada paket bundling yang dibuat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection