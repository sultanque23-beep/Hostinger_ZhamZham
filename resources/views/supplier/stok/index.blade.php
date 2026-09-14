<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Supplier - Restock Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6 block">
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Portal Supplier - Restock Barang</h2>
                <p class="text-sm text-gray-500">Pilih barang dan masukkan jumlah stok tambahan yang dikirim ke toko.</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-2 rounded font-bold">
                    Logout
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-700 uppercase text-xs">
                        <th class="p-3">Kode / ID</th>
                        <th class="p-3">Nama Barang</th>
                        <th class="p-3">Stok Saat Ini</th>
                        <th class="p-3 text-center">Aksi Tambah Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-mono text-sm">{{ $item->kode_barcode ?? $item->barcode ?? $item->id }}</td>
                            <td class="p-3 font-semibold text-gray-800">{{ $item->nama_barang ?? $item->name }}</td>
                            <td class="p-3">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 font-bold rounded-full text-sm">
                                    {{ $item->stok ?? $item->stock ?? 0 }}
                                </span>
                            </td>
                            <td class="p-3">
                                <form action="{{ route('supplier.stok.add', $item->id) }}" method="POST" class="flex gap-2 justify-center items-center">
                                    @csrf
                                    <input type="number" name="jumlah_stok" placeholder="+ Qty" class="w-24 border border-gray-300 rounded px-3 py-1 text-sm" min="1" required>
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-1.5 rounded text-sm">
                                        + Tambah Stok
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data barang di database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>