<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barang Terlaris Bulan Ini - Grosir ZhamZham</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                padding: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-zinc-800 min-h-screen py-8 text-black">

    <!-- TOMBOL AKSI TOP BAR -->
    <div class="max-w-md mx-auto mb-6 flex justify-center gap-3 no-print">
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-lg text-sm flex items-center gap-2 shadow-md cursor-pointer transition">
            🖨️ Mulai Cetak Masal
        </button>
        <a href="javascript:history.back()" class="bg-slate-700 hover:bg-slate-800 text-white font-bold py-2 px-5 rounded-lg text-sm flex items-center gap-2 shadow-md transition">
            🏠 Kembali
        </a>
    </div>

    <!-- WRAPPER STRUK / LAPORAN -->
    <div class="print-container max-w-md mx-auto bg-white p-6 shadow-2xl rounded-sm">
        
        <!-- HEADER STRUK -->
        <div class="text-center font-bold">
            <h1 class="text-base tracking-widest uppercase">GROSIR ZHAMZHAM</h1>
            <p class="text-xs font-normal">Sembako & Kebutuhan Pokok</p>
            <p class="text-xs font-normal mt-1">LAPORAN BARANG TERLARIS</p>
            <p class="text-xs font-normal">Periode: {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
            <p class="text-[11px] font-normal text-zinc-600">Tgl Cetak: {{ date('d/m/Y H:i') }}</p>
        </div>

        <div class="my-2 text-center text-xs tracking-tighter overflow-hidden">
            --------------------------------------------------
        </div>

        <!-- HEADER TABEL -->
        <div class="text-xs font-bold flex justify-between px-1">
            <span class="w-1/2">Item</span>
            <span class="w-1/6 text-center">Qty</span>
            <span class="w-1/3 text-right">Total Omset</span>
        </div>

        <div class="my-2 text-center text-xs tracking-tighter overflow-hidden">
            --------------------------------------------------
        </div>

        <!-- LIST BARANG TERLARIS -->
        <div class="text-xs space-y-3">
            @forelse($products as $index => $item)
                <div>
                    <div class="font-bold">
                        {{ $index + 1 }}. {{ $item->name }}
                    </div>
                    <div class="flex justify-between text-zinc-700 pl-4 mt-0.5">
                        <span>@ Rp {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                        <span class="font-bold text-black">{{ number_format($item->total_qty, 0, ',', '.') }} pcs</span>
                        <span class="font-bold text-black">Rp {{ number_format($item->total_omset, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-zinc-500 italic">
                    Belum ada data penjualan pada bulan ini.
                </div>
            @endforelse
        </div>

        <div class="my-2 text-center text-xs tracking-tighter overflow-hidden">
            --------------------------------------------------
        </div>

        <!-- RINGKASAN TOTAL -->
        @php
            $grandTotalQty = $products->sum('total_qty');
            $grandTotalOmset = $products->sum('total_omset');
        @endphp

        <div class="text-xs font-bold space-y-1">
            <div class="flex justify-between">
                <span>TOTAL ITEM TERJUAL:</span>
                <span>{{ number_format($grandTotalQty, 0, ',', '.') }} pcs</span>
            </div>
            <div class="flex justify-between text-sm pt-1 border-t border-dashed border-zinc-400">
                <span>TOTAL OMSET:</span>
                <span>Rp {{ number_format($grandTotalOmset, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-4 text-center text-xs tracking-tighter overflow-hidden">
            --------------------------------------------------
        </div>

        <!-- FOOTER -->
        <div class="text-center text-xs mt-2">
            <p>-- Laporan Ringkasan Performa Produk --</p>
        </div>

    </div>

</body>
</html>