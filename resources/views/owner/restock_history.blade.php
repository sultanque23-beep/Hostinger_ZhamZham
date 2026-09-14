@extends('layouts.app')

@section('title', 'Riwayat Restock Barang')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900">📦 Riwayat Barang Masuk (Restock Supplier)</h2>
            <p class="text-xs font-semibold text-slate-400">Daftar riwayat penambahan stok barang yang dikirim oleh supplier</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-sm">📄 Log Restock Terakhir</h3>
        </div>
        <form action="{{ route('owner.restock.history.reset') }}" method="POST" class="inline-block" onsubmit="return confirm('⚠️ Yakin ingin MENGHAPUS SEMUA riwayat restock? Data yang terhapus tidak dapat dikembalikan.')">
    @csrf
    @method('DELETE')
    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-2 px-4 rounded-xl border border-rose-200 shadow-sm transition text-xs flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-trash-can"></i> Kosongkan Riwayat
    </button>
</form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                        <th class="p-3">Waktu Masuk</th>
                        <th class="p-3">Nama Supplier</th>
                        <th class="p-3">Nama Barang</th>
                        <th class="p-3 text-center">Jumlah Masuk</th>
                        <th class="p-3 text-right">Harga Beli Satuan</th>
                        <th class="p-3 text-right">Total Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($histories as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-slate-500">
                                {{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="p-3 font-bold text-slate-900">
                                {{ $row->nama_supplier ?? 'Supplier' }}
                            </td>
                            <td class="p-3 text-slate-800">
                                {{ $row->nama_barang ?? 'Barang Dihapus' }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1 rounded-full text-[11px]">
                                    +{{ $row->jumlah }}
                                </span>
                            </td>
                            <td class="p-3 text-right text-slate-700">
                                Rp {{ number_format($row->harga_beli ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-bold text-slate-900">
                                Rp {{ number_format($row->total_harga ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-3xl">📦</span>
                                    <p>Belum ada riwayat barang masuk dari supplier.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($histories, 'links'))
            <div class="p-4 border-t border-slate-100">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection