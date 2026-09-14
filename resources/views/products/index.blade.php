@extends('layouts.app')

@section('title', 'Daftar Stok Barang - Grosir ZhamZham')

@section('content')

<!-- STYLE KHUSUS SAAT CETAK / SIMPAN KE PDF -->
<style>
    @media print {
        /* Sembunyikan elemen UI yang tidak perlu di dalam PDF */
        aside, nav, header, #floatingBar, #searchInput, #btnSelectAll, #btnUnselectAll, 
        .no-print, button, a, .pagination, form#globalDeleteForm {
            display: none !important;
        }

        body {
            background-color: white !important;
            color: black !important;
            font-size: 10pt !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Tampilkan Header Khusus Laporan PDF */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        /* Lebarkan tabel agar memenuhi kertas */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th, td {
            border: 1px solid #ddd !important;
            padding: 6px !important;
            font-size: 9pt !important;
        }

        th {
            background-color: #f3f4f6 !important;
            color: black !important;
        }

        /* SEMBUNYIKAN BARANG YANG BELUM TERJUAL SAAT MENCETAK FILTER TERLARIS */
        body.print-only-sold .unsold-item {
            display: none !important;
        }
    }

    /* Sembunyikan Header Cetak di Tampilan Layar Biasa */
    .print-header {
        display: none;
    }
</style>

<!-- HEADER KHUSUS YANG AKAN MUNCUL DI PDF -->
<div class="print-header">
    <h1 style="font-size: 18pt; font-weight: bold; margin: 0;">GROSIR ZHAMZHAM</h1>
    <p style="font-size: 11pt; margin: 4px 0; font-weight: bold;">
        {{ request('filter') == 'terlaris' ? 'LAPORAN BARANG TERLARIS (HANYA YANG SUDAH TERJUAL)' : 'LAPORAN DATA STOK BARANG' }}
    </p>
    <p style="font-size: 9pt; color: #555; margin: 0;">Dicetak pada: {{ date('d-m-Y H:i') }} WIB</p>
</div>

<!-- FORM UTAMA UNTUK RESTOCK MASSAL -->
<form action="{{ route('products.restock-massal') }}" method="POST" id="formRestockMassal">
    @csrf
    
    <div class="space-y-8">
        
        <!-- HEADER PAGE -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 no-print">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Stok Barang</h2>
                <p class="text-xs font-semibold text-slate-400 mt-0.5">Grosir ZhamZham • Sistem Kontrol Inventori & Early Warning System</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <!-- TOMBOL CETAK SESUAI FILTER -->
                <button type="button" onclick="printFilteredReport()" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 text-sm rounded-xl shadow-sm transition duration-150 cursor-pointer">
                    <i class="fa-solid fa-file-pdf text-xs"></i> 
                    {{ request('filter') == 'terlaris' ? 'Cetak Laporan Terlaris' : 'Cetak / Simpan PDF' }}
                </button>

                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 text-sm rounded-xl shadow-sm transition duration-150">
                    <i class="fa-solid fa-plus text-xs"></i> Tambah Barang Baru
                </a>
            </div>
        </div>

        <!-- NOTIFIKASI FLASH MESSAGE -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2 no-print">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>✨ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2 no-print">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                <span>⚠️ {{ session('error') }}</span>
            </div>
        @endif

        <!-- BARIS INPUT PENCARIAN BARANG & FILTER EWS -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center no-print">
            
            <!-- Pencarian -->
            <div class="w-full md:w-72">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Cari nama barang atau barcode..." class="w-full pl-9 pr-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-slate-50/50" onkeydown="handleSearchEnter(event)">
                </div>
            </div>

            <!-- AKSI SELEKSI MASSAL RESTOCK -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="button" id="btnSelectAll" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs px-3.5 py-2 rounded-xl border border-indigo-200 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check-double text-xs"></i> Beli Semua
                </button>
                <button type="button" id="btnUnselectAll" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs px-3.5 py-2 rounded-xl border border-slate-200 transition cursor-pointer">
                    Batal Semua
                </button>
            </div>

            <!-- Filter Cepat EWS & Analisis Produk -->
            <div class="flex flex-wrap gap-1.5 items-center w-full md:w-auto">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1">
                    <i class="fa-solid fa-filter text-emerald-600"></i> Filter:
                </span>
                
                @php
                    $searchParam = request('search') ? ['search' => request('search')] : [];
                @endphp

                <!-- Filter Semua -->
                <a href="{{ route('products.index', $searchParam) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ !request('filter') ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                    📋 Semua
                </a>

                <!-- Filter Terlaris (Khusus barang yang sudah terjual) -->
                <a href="{{ route('products.index', array_merge($searchParam, ['filter' => 'terlaris'])) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ request('filter') == 'terlaris' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-indigo-50 text-indigo-700 border-indigo-100 hover:bg-indigo-100/70' }}">
                    🔥 Terlaris (Sudah Terjual)
                </a>

                <!-- Filter Termahal -->
                <a href="{{ route('products.index', array_merge($searchParam, ['filter' => 'termahal'])) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ request('filter') == 'termahal' ? 'bg-purple-600 text-white border-purple-600' : 'bg-purple-50 text-purple-700 border-purple-100 hover:bg-purple-100/70' }}">
                    💎 Termahal
                </a>

                <!-- Filter Expired -->
                <a href="{{ route('products.index', array_merge($searchParam, ['filter' => 'expired'])) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ request('filter') == 'expired' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-100/70' }}">
                    🔴 Expired
                </a>

                <!-- Filter ED Dekat -->
                <a href="{{ route('products.index', array_merge($searchParam, ['filter' => 'warning'])) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ request('filter') == 'warning' ? 'bg-amber-500 text-white border-amber-500' : 'bg-amber-50 text-amber-700 border-amber-100 hover:bg-amber-100/70' }}">
                    ⚠️ Expire Waktu Dekat
                </a>

                <!-- Filter Stok Rendah -->
                <a href="{{ route('products.index', array_merge($searchParam, ['filter' => 'low_stock'])) }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl transition-all border {{ request('filter') == 'low_stock' ? 'bg-orange-500 text-white border-orange-500' : 'bg-orange-50 text-orange-700 border-orange-100 hover:bg-orange-100/70' }}">
                    🔢 Stok ≤10
                </a>
            </div>
        </div>

        <!-- TABEL MANAJEMEN STOK -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs" id="productTable">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="p-4 w-10 text-center no-print">
                                <input type="checkbox" id="checkMaster" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                            </th>
                            <th class="p-4 w-12 text-center">No</th>
                            <th class="p-4 w-28 text-center">Barcode</th>
                            <th class="p-4">Nama Barang Sembako</th>
                            <th class="p-4 w-20 text-center">Stok</th>
                            <th class="p-4 text-right">Harga Beli</th>
                            <th class="p-4 text-right">Harga Jual</th>
                            @if(request('filter') == 'terlaris')
                                <th class="p-4 w-28 text-center text-indigo-600">Total Terjual</th>
                            @endif
                            <th class="p-4 w-28 text-center">Kedaluwarsa</th>
                            <th class="p-4 w-28 text-center no-print">Qty Beli</th>
                            <th class="p-4 w-44 text-center no-print">Status & Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                        @forelse($products as $index => $product)
                            @php
                                $totalTerjual = $product->total_terjual ?? $product->sales_count ?? 0;
                                $isUnsold = ($totalTerjual <= 0);

                                $dateField = $product->expired_at ?? $product->expired_date;
                                $rowBg = "hover:bg-slate-50/80"; 
                                
                                if ($dateField) {
                                    $expiredDate = \Carbon\Carbon::parse($dateField)->startOfDay();
                                    $today = \Carbon\Carbon::now()->startOfDay();
                                    $daysLeft = $today->diffInDays($expiredDate, false);
                                    
                                    if ($daysLeft < 0) {
                                        $rowBg = "bg-rose-50/40 hover:bg-rose-50";
                                    } elseif ($daysLeft <= 30) {
                                        $rowBg = "bg-amber-50/40 hover:bg-amber-50";
                                    }
                                }
                                
                                $itemNumber = method_exists($products, 'firstItem') ? ($products->firstItem() + $index) : ($index + 1);
                            @endphp

                            <!-- MENAMBAHKAN CLASS 'unsold-item' JIKA BARANG BELUM TERJUAL -->
                            <tr class="product-row {{ $rowBg }} {{ $isUnsold ? 'unsold-item' : 'sold-item' }} transition duration-150" id="row-{{ $product->id }}">
                                <!-- CHECKBOX RESTOCK -->
                                <td class="p-4 text-center no-print">
                                    <input type="checkbox" name="selected_items[]" value="{{ $product->id }}" class="item-checkbox w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer" onchange="toggleBuyStatus('{{ $product->id }}')">
                                </td>

                                <td class="p-4 text-center text-slate-400 font-bold">{{ $itemNumber }}</td>
                                <td class="p-4 text-center font-mono font-bold text-slate-700">{{ $product->product_code }}</td>
                                <td class="p-4 font-bold text-slate-900 text-xs">{{ $product->name }}</td>
                                
                                <!-- BADGE STOK -->
                                <td class="p-4 text-center font-bold">
                                    <span class="{{ $product->stock <= 10 ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100' : 'text-slate-800' }}">{{ $product->stock }}</span>
                                </td>

                                <td class="p-4 text-right font-semibold text-slate-500">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                                <td class="p-4 text-right font-bold text-slate-900">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                
                                <!-- TOTAL TERJUAL -->
                                @if(request('filter') == 'terlaris')
                                    <td class="p-4 text-center font-black text-indigo-600 bg-indigo-50/30">
                                        {{ number_format($totalTerjual, 0, ',', '.') }} item
                                    </td>
                                @endif

                                <td class="p-4 text-center font-semibold text-slate-500">
                                    {{ $dateField ? \Carbon\Carbon::parse($dateField)->format('d-m-Y') : '-' }}
                                </td>

                                <!-- INPUT QTY RESTOCK -->
                                <td class="p-4 text-center no-print">
                                    <input type="number" name="qty[{{ $product->id }}]" id="qty-{{ $product->id }}" placeholder="+ Qty" min="1" value="10" disabled class="w-16 text-center bg-slate-100 border border-slate-300 rounded-lg py-1 px-1.5 text-xs font-bold focus:ring-2 focus:ring-emerald-500 disabled:opacity-40">
                                </td>

                                <!-- STATUS & AKSI -->
                                <td class="p-4 no-print">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" id="btn-status-{{ $product->id }}" onclick="toggleStatusBtn('{{ $product->id }}')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold border border-slate-200 bg-slate-100 text-slate-400 transition flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i> Tidak Beli
                                        </button>

                                        <div class="flex items-center gap-1 border-l pl-1.5 border-slate-200">
                                            <a href="{{ route('products.edit', $product->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-1.5 rounded-lg transition" title="Edit Barang">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>
                                            
                                            <button type="button" 
                                                    data-url="{{ route('products.destroy', $product->id) }}" 
                                                    onclick="deleteProduct(this, '{{ addslashes($product->name) }}')" 
                                                    class="bg-rose-50 hover:bg-rose-100 text-rose-600 p-1.5 rounded-lg transition cursor-pointer" 
                                                    title="Hapus Barang">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('filter') == 'terlaris' ? 11 : 10 }}" class="p-12 text-center text-slate-400 font-medium bg-slate-50/30">
                                    <i class="fa-solid fa-boxes-packing text-2xl mb-2 text-slate-300 block"></i>
                                    Belum ada data produk sembako yang sesuai di dalam database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if(method_exists($products, 'hasPages') && $products->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 no-print">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- FLOATING BOTTOM BAR -->
    <div id="floatingBar" class="fixed bottom-6 right-6 left-6 md:left-72 bg-slate-900/90 backdrop-blur-md text-white p-4 rounded-2xl shadow-2xl border border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300 transform translate-y-32 opacity-0 z-50 no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-cart-flatbed"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-200">Pengajuan Restock Siap Dikirim</p>
                <p class="text-[11px] text-slate-400"><span id="floatingCount" class="text-emerald-400 font-bold">0</span> produk terpilih untuk dibeli ke supplier</p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select name="supplier_id" required class="bg-slate-800 text-white text-xs font-bold py-2.5 px-3 rounded-xl border border-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer w-full sm:w-auto">
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs px-6 py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-paper-plane"></i> Konfirmasi Pengajuan Restock
            </button>
        </div>
    </div>
</form>

<!-- FORM HAPUS -->
<form id="globalDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('.product-row');
    const checkMaster = document.getElementById('checkMaster');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnUnselectAll = document.getElementById('btnUnselectAll');

    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') return;
            
            const query = this.value.toLowerCase().trim();

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            updateCheckMasterState();
        });
    }

    function getVisibleCheckboxes() {
        return Array.from(checkboxes).filter(cb => {
            const row = cb.closest('.product-row');
            return row && row.style.display !== 'none';
        });
    }

    if (checkMaster) {
        checkMaster.addEventListener('change', function() {
            const visibleCbs = getVisibleCheckboxes();
            visibleCbs.forEach(cb => {
                cb.checked = this.checked;
                toggleBuyStatus(cb.value);
            });
        });
    }

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            const visibleCbs = getVisibleCheckboxes();
            visibleCbs.forEach(cb => {
                cb.checked = true;
                toggleBuyStatus(cb.value);
            });
            updateCheckMasterState();
        });
    }

    if (btnUnselectAll) {
        btnUnselectAll.addEventListener('click', function() {
            checkboxes.forEach(cb => {
                cb.checked = false;
                toggleBuyStatus(cb.value);
            });
            if (checkMaster) checkMaster.checked = false;
        });
    }
});

// FUNSI CETAK PDF DENGAN FILTERING BARANG YANG SUDAH TERJUAL
function printFilteredReport() {
    const isTerlaris = "{{ request('filter') }}" === "terlaris";
    
    if (isTerlaris) {
        document.body.classList.add('print-only-sold');
    }

    window.print();

    setTimeout(() => {
        document.body.classList.remove('print-only-sold');
    }, 1000);
}

function handleSearchEnter(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const searchVal = event.target.value;
        const url = new URL(window.location.href);
        
        if (searchVal) {
            url.searchParams.set('search', searchVal);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
}

function toggleBuyStatus(id) {
    const cb = document.querySelector(`.item-checkbox[value="${id}"]`);
    const btnStatus = document.getElementById(`btn-status-${id}`);
    const qtyInput = document.getElementById(`qty-${id}`);

    if (cb && btnStatus && qtyInput) {
        if (cb.checked) {
            btnStatus.className = "px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-500 text-white shadow-sm border border-emerald-600 transition flex items-center gap-1 cursor-pointer";
            btnStatus.innerHTML = `<i class="fa-solid fa-cart-shopping"></i> Beli`;
            qtyInput.disabled = false;
            qtyInput.classList.remove('bg-slate-100');
            qtyInput.classList.add('bg-white');
        } else {
            btnStatus.className = "px-2.5 py-1 rounded-lg text-[10px] font-bold border border-slate-200 bg-slate-100 text-slate-400 transition flex items-center gap-1 cursor-pointer";
            btnStatus.innerHTML = `<i class="fa-solid fa-xmark"></i> Tidak Beli`;
            qtyInput.disabled = true;
            qtyInput.classList.add('bg-slate-100');
            qtyInput.classList.remove('bg-white');
        }
    }

    updateSummary();
    updateCheckMasterState();
}

function toggleStatusBtn(id) {
    const cb = document.querySelector(`.item-checkbox[value="${id}"]`);
    if (cb) {
        cb.checked = !cb.checked;
        toggleBuyStatus(id);
    }
}

function updateSummary() {
    const selected = document.querySelectorAll('.item-checkbox:checked').length;
    const floatingCount = document.getElementById('floatingCount');
    const floatingBar = document.getElementById('floatingBar');

    if(floatingCount) floatingCount.innerText = selected;

    if (floatingBar) {
        if (selected > 0) {
            floatingBar.classList.remove('translate-y-32', 'opacity-0');
            floatingBar.classList.add('translate-y-0', 'opacity-100');
        } else {
            floatingBar.classList.add('translate-y-32', 'opacity-0');
            floatingBar.classList.remove('translate-y-0', 'opacity-100');
        }
    }
}

function updateCheckMasterState() {
    const checkMaster = document.getElementById('checkMaster');
    if (!checkMaster) return;

    const visibleCbs = Array.from(document.querySelectorAll('.item-checkbox')).filter(cb => {
        const row = cb.closest('.product-row');
        return row && row.style.display !== 'none';
    });

    if (visibleCbs.length === 0) {
        checkMaster.checked = false;
        return;
    }

    const allChecked = visibleCbs.every(cb => cb.checked);
    checkMaster.checked = allChecked;
}

function deleteProduct(buttonElement, name) {
    const url = buttonElement.getAttribute('data-url');
    if (confirm(`Yakin ingin menghapus ${name}?`)) {
        const form = document.getElementById('globalDeleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection