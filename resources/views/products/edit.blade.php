@extends('layouts.app')

@section('title', 'Edit Barang - Grosir ZhamZham')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <div>
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1 mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Stok
        </a>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">📝 Perbarui Data Barang</h2>
        <p class="text-xs font-semibold text-slate-400 mt-0.5">Ubah informasi stok, harga eceran, harga grosir, atau tanggal expired produk</p>
    </div>

    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <form action="{{ route('products.update', $product->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kode Barcode / Product Code</label>
                    <input type="text" name="product_code" value="{{ $product->product_code }}" required class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-slate-800 focus:outline-none transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Barang Sembako</label>
                    <input type="text" name="name" value="{{ $product->name }}" required class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penyesuaian Stok Gudang</label>
                    <input type="number" name="stock" value="{{ $product->stock }}" required min="0" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-slate-800 focus:outline-none transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Expired</label>
                    @php 
                        $currentDate = $product->expired_at ?? $product->expired_date;
                        $formattedDate = $currentDate ? \Carbon\Carbon::parse($currentDate)->format('Y-m-d') : '';
                    @endphp
                    <input type="date" name="expired_at" value="{{ $formattedDate }}" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga Beli Modal (Rp)</label>
                    <input type="number" name="purchase_price" value="{{ $product->purchase_price }}" required min="0" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-slate-800 focus:outline-none transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga Jual Toko / Eceran (Rp)</label>
                    <input type="number" name="selling_price" value="{{ $product->selling_price }}" required min="0" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-emerald-600 focus:outline-none transition">
                </div>

                <!-- DITAMBAHKAN: HARGA GROSIR -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Harga Grosir (Rp) <span class="text-slate-400 font-normal">(Boleh Kosong)</span></label>
                    <input type="number" name="wholesale_price" value="{{ $product->wholesale_price }}" min="0" placeholder="0" class="w-full bg-emerald-50/50 border-2 border-emerald-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-emerald-700 focus:outline-none transition">
                </div>

                <!-- DITAMBAHKAN: MINIMAL QTY GROSIR -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Min. Qty Pembelian Grosir</label>
                    <input type="number" name="wholesale_min_qty" value="{{ $product->wholesale_min_qty ?? 0 }}" min="0" placeholder="Contoh: 12" class="w-full bg-emerald-50/50 border-2 border-emerald-200 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-2.5 text-sm font-money font-bold text-emerald-700 focus:outline-none transition">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('products.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 px-5 rounded-xl text-xs transition cursor-pointer">Batal</a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2.5 px-6 rounded-xl text-xs shadow-md shadow-emerald-100 transition cursor-pointer">Update Data</button>
            </div>
        </form>
    </div>
</div>
@endsection