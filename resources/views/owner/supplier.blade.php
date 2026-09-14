@extends('layouts.app') {{-- Sesuaikan dengan nama file master layout Anda jika ada --}}

@section('content')
<div class="container-fluid padding-20">
    <h2 style="font-weight: bold; margin-bottom: 20px;">Manajemen Data Supplier</h2>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <!-- Form Tambah Supplier Baru -->
        <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            <h4 style="font-weight: bold; margin-bottom: 15px;">+ Tambah Supplier Baru</h4>
            
            @if(session('success'))
                <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('supplier.store') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nama Supplier / PT</label>
                    <input type="text" name="nama_supplier" class="form-control" placeholder="Contoh: PT Gudang Garam Tbk" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Alamat</label>
                    <textarea name="alamat" rows="3" class="form-control" placeholder="Contoh: Jl. Raya Industri No. 45, Bandung" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
                </div>

                <button type="submit" style="background-color: #00a65a; color: white; border: none; padding: 10px 15px; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%;">
                    Simpan Supplier
                </button>
            </form>
        </div>

        <!-- Tabel Daftar Supplier -->
        <div style="flex: 2; min-width: 400px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            <h4 style="font-weight: bold; margin-bottom: 15px;">Daftar Supplier</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 10px;">NO</th>
                        <th style="padding: 10px;">NAMA SUPPLIER</th>
                        <th style="padding: 10px;">NO. HP</th>
                        <th style="padding: 10px;">ALAMAT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $key => $s)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">{{ $key + 1 }}</td>
                        <td style="padding: 10px; font-weight: bold;">{{ $s->nama_supplier }}</td>
                        <td style="padding: 10px;">{{ $s->no_hp ?? '-' }}</td>
                        <td style="padding: 10px;">{{ $s->alamat ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #888;">Belum ada data supplier. Tambahkan di form sebelah kiri.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection