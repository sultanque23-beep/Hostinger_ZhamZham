@extends('layouts.app')

@section('title', 'Meja Kasir - Grosir ZhamZham')

@section('content')
<!-- Import Library Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: #e2e8f0 !important;
        border-radius: 0.75rem !important;
        background-color: #f8fafc !important;
        padding-top: 6px !important;
        padding-bottom: 6px !important;
        padding-left: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        border-color: #e2e8f0 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1) !important;
        font-size: 0.875rem !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }
    .select2-search__field {
        border-radius: 0.5rem !important;
        border-color: #cbd5e1 !important;
        padding: 6px 10px !important;
        outline: none !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #059669 !important;
    }
</style>

<div class="space-y-8">
    
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Meja Kasir Utama</h2>
        <p class="text-xs font-semibold text-slate-400 mt-0.5">Input belanjaan eceran maupun grosir dengan perhitungan otomatis</p>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex flex-col gap-1">
            <div class="flex items-center gap-2 font-bold text-rose-700">
                <i class="fa-solid fa-circle-xmark"></i> Gagal Memproses Transaksi:
            </div>
            <ul class="list-disc list-inside text-xs pl-2 text-rose-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3.5 rounded-xl text-sm font-semibold shadow-sm flex flex-col gap-1 mb-4">
            <div class="flex items-center gap-2 font-bold text-amber-700">
                <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kendala Sistem Database:
            </div>
            <p class="text-xs text-amber-600 pl-2 font-mono bg-amber-100/50 p-2 rounded-lg mt-1">
                {{ session('error') }}
            </p>
        </div>
    @endif

    <form action="{{ route('transactions.store') }}" method="POST" id="form_checkout">
        @csrf
        
        <!-- MULTI HIDDEN INPUT UNTUK MENGANTISIPASI PEMBACAAN NAMA VARIABLE DI CONTROLLER -->
        <input type="hidden" name="payment_method" id="input_payment_method" value="tunai">
        <input type="hidden" name="payment_type" id="input_payment_type" value="tunai">
        <input type="hidden" name="metode_pembayaran" id="input_metode_pembayaran" value="tunai">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="lg:col-span-2 space-y-6">
                
                {{-- INPUT SCAN BARCODE ATAU KETIK NAMA PRODUK --}}
                <div class="mb-4 space-y-1.5">
                    <label for="barcode_scanner" class="text-xs font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-barcode text-sm"></i> Scan Barcode / Ketik Nama Produk Di Sini
                    </label>
                    <input type="text" id="barcode_scanner" list="products_datalist" autofocus placeholder="Scan barcode atau ketik nama produk / paket..." class="w-full bg-emerald-50/50 border-2 border-emerald-300 focus:border-emerald-500 focus:bg-white rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:outline-none transition shadow-inner">
                    
                    <datalist id="products_datalist">
                        @foreach($products as $product)
                            @php
                                $price = $product->selling_price ?? $product->price ?? 0;
                                $code = $product->product_code ?? $product->code ?? '';
                            @endphp
                            <option value="{{ $product->name }}">Kode: {{ $code }} | Rp {{ number_format($price, 0, ',', '.') }}</option>
                        @endforeach
                        @if(isset($bundlings) && $bundlings->count() > 0)
                            @foreach($bundlings as $bundle)
                                @php
                                    $bName = $bundle->bundle_name ?? $bundle->name ?? '';
                                    $bPrice = $bundle->bundle_price ?? $bundle->price ?? 0;
                                    $bCode = $bundle->bundle_code ?? $bundle->code ?? '';
                                @endphp
                                <option value="[PAKET] {{ $bName }}">Kode: {{ $bCode }} | Rp {{ number_format($bPrice, 0, ',', '.') }}</option>
                            @endforeach
                        @endif
                    </datalist>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-emerald-600"></i> Pilih & Tambah Barang / Paket
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="product_select" class="text-xs font-bold text-slate-500">Nama Produk atau Paket Bundling</label>
                            
                            <select id="product_select" class="w-full">
                                <option value="">-- Pilih Produk Belanjaan --</option>
                                
                                <optgroup label="Produk Eceran / Grosir">
                                    @foreach($products as $product)
                                        @php
                                            $price = $product->selling_price ?? $product->price ?? 0;
                                            $wholesalePrice = $product->wholesale_price ?? $product->harga_grosir ?? $product->grosir_price ?? '';
                                            $wholesaleQty = $product->wholesale_min_qty ?? $product->min_grosir ?? $product->min_wholesale_qty ?? 0;
                                            $code = $product->product_code ?? $product->code ?? '';
                                        @endphp
                                        <option value="{{ $product->id }}" 
                                                data-type="single" 
                                                data-price="{{ $price }}" 
                                                data-wholesale-price="{{ $wholesalePrice }}"
                                                data-wholesale-qty="{{ $wholesaleQty }}"
                                                data-stock="{{ $product->stock }}" 
                                                data-code="{{ $code }}">
                                            {{ $product->name }} (Stok: {{ $product->stock }} | Rp {{ number_format($price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </optgroup>

                                @if(isset($bundlings) && $bundlings->count() > 0)
                                    <optgroup label="🎁 Paket Bundling Promo">
                                        @foreach($bundlings as $bundle)
                                            @php
                                                $bName = $bundle->bundle_name ?? $bundle->name ?? '';
                                                $bPrice = $bundle->bundle_price ?? $bundle->price ?? 0;
                                                $bCode = $bundle->bundle_code ?? $bundle->code ?? '';
                                            @endphp
                                            <option value="{{ $bundle->id }}" 
                                                    data-type="bundling" 
                                                    data-price="{{ $bPrice }}" 
                                                    data-wholesale-price=""
                                                    data-wholesale-qty="0"
                                                    data-stock="999" 
                                                    data-code="{{ $bCode }}">
                                                [PAKET] {{ $bName }} (Rp {{ number_format($bPrice, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="product_qty" class="text-xs font-bold text-slate-500">Jumlah (Qty)</label>
                            <div class="flex gap-2">
                                <input type="number" id="product_qty" value="1" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-money font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white text-center transition duration-150">
                                <button type="button" id="btn_add_item" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold p-2.5 rounded-xl transition duration-150 shadow-sm cursor-pointer">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-4 bg-slate-50/70 border-b border-slate-200 font-bold text-slate-800 text-xs">
                        🛒 Daftar Keranjang Belanjaan Kasir
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                                    <th class="p-4">Nama Produk</th>
                                    <th class="p-4 w-32 text-center">Harga Satuan</th>
                                    <th class="p-4 w-28 text-center">Qty</th>
                                    <th class="p-4 w-32 text-right pr-6">Subtotal</th>
                                    <th class="p-4 w-16 text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="cart_table_body" class="divide-y divide-slate-100 text-slate-600 font-medium">
                                <tr id="cart_empty_row">
                                    <td colspan="5" class="p-12 text-center text-slate-400 font-medium bg-slate-50/20">
                                        Keranjang masih kosong. Pilih atau ketik produk di atas untuk memulai transaksi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white border-2 border-emerald-600 rounded-3xl shadow-xl p-6 space-y-6 sticky top-6">
                    <div class="bg-slate-900 text-emerald-400 p-5 rounded-2xl text-center shadow-inner space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Akhir Belanja</span>
                        <div class="text-3xl font-extrabold font-money tracking-tight" id="display_total">Rp 0</div>
                        <input type="hidden" name="total_price" id="input_total_price" value="0">
                    </div>

                    <div class="space-y-4 pt-2">
                        <!-- PILIHAN METODE PEMBAYARAN -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pilih Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" id="btn_mode_tunai" class="py-2.5 px-3 rounded-xl border-2 border-emerald-600 bg-emerald-50 text-emerald-700 font-extrabold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer shadow-sm">
                                    <i class="fa-solid fa-money-bill-wave"></i> Tunai
                                </button>
                                <button type="button" id="btn_mode_qris" class="py-2.5 px-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer">
                                    <i class="fa-solid fa-qrcode"></i> QRIS
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="pay_amount" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Uang Tunai / QRIS (Rp)</label>
                            <input type="number" name="pay_amount" id="pay_amount" required min="0" placeholder="0" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-2xl px-4 py-3 text-lg font-money font-bold text-slate-900 text-right focus:outline-none transition duration-150">
                        </div>

                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kembalian</span>
                            <span class="text-lg font-extrabold font-money text-slate-900" id="display_change">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" id="btn_submit_transaction" disabled class="w-full bg-slate-300 text-slate-500 font-black py-4 px-6 rounded-2xl text-sm tracking-wide shadow-md uppercase transition duration-150 cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cash-register"></i> Bayar & Cetak Nota
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Import jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#product_select').select2({
        placeholder: "-- Pilih Produk Belanjaan --",
        allowClear: true,
        width: '100%'
    });

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });

    const productSelect = document.getElementById('product_select');
    const productQty = document.getElementById('product_qty');
    const btnAddItem = document.getElementById('btn_add_item');
    const cartTableBody = document.getElementById('cart_table_body');
    const cartEmptyRow = document.getElementById('cart_empty_row');
    const displayTotal = document.getElementById('display_total');
    const inputTotalPrice = document.getElementById('input_total_price');
    const payAmountInput = document.getElementById('pay_amount');
    const displayChange = document.getElementById('display_change');
    const btnSubmit = document.getElementById('btn_submit_transaction');
    const barcodeScanner = document.getElementById('barcode_scanner');
    const btnModeTunai = document.getElementById('btn_mode_tunai');
    const btnModeQris = document.getElementById('btn_mode_qris');
    
    // Hidden inputs
    const inputPaymentMethod = document.getElementById('input_payment_method');
    const inputPaymentType = document.getElementById('input_payment_type');
    const inputMetodePembayaran = document.getElementById('input_metode_pembayaran');

    let cartData = [];
    let currentPaymentMode = 'tunai';

    // FUNGSI GANTI METODE PEMBAYARAN (TUNAI / QRIS)
    function setPaymentMode(mode) {
        currentPaymentMode = mode;
        
        // Update nilai ke semua variasi hidden input
        if(inputPaymentMethod) inputPaymentMethod.value = mode;
        if(inputPaymentType) inputPaymentType.value = mode;
        if(inputMetodePembayaran) inputMetodePembayaran.value = mode;

        const currentTotal = parseInt(inputTotalPrice.value) || 0;

        if (mode === 'qris') {
            // Tampilan Tombol QRIS Aktif
            btnModeQris.className = "py-2.5 px-3 rounded-xl border-2 border-emerald-600 bg-emerald-50 text-emerald-700 font-extrabold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer shadow-sm";
            btnModeTunai.className = "py-2.5 px-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer";
            
            // Lock input nominal untuk QRIS & samakan dengan total
            payAmountInput.value = currentTotal > 0 ? currentTotal : 0;
            payAmountInput.readOnly = true;
            payAmountInput.classList.add('bg-slate-100');
        } else {
            // Tampilan Tombol Tunai Aktif
            btnModeTunai.className = "py-2.5 px-3 rounded-xl border-2 border-emerald-600 bg-emerald-50 text-emerald-700 font-extrabold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer shadow-sm";
            btnModeQris.className = "py-2.5 px-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs flex items-center justify-center gap-2 transition duration-150 cursor-pointer";
            
            // Unlock input nominal untuk Tunai
            payAmountInput.readOnly = false;
            payAmountInput.classList.remove('bg-slate-100');
            payAmountInput.value = '';
            payAmountInput.focus();
        }

        updateTotals(currentTotal);
    }

    btnModeTunai.addEventListener('click', () => setPaymentMode('tunai'));
    btnModeQris.addEventListener('click', () => setPaymentMode('qris'));

    function calculatePrice(item, qty) {
        if (item.type === 'single' && item.wholesalePrice && item.wholesaleMinQty > 0) {
            if (qty >= item.wholesaleMinQty) {
                return { price: item.wholesalePrice, isWholesale: true };
            }
        }
        return { price: item.regularPrice, isWholesale: false };
    }

    // SCANNER & BARCODE
    if (barcodeScanner) {
        barcodeScanner.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim().toLowerCase();
                if (!query) return;

                let matchedValue = null;

                for (let i = 0; i < productSelect.options.length; i++) {
                    const option = productSelect.options[i];
                    if (!option.value) continue;

                    const code = option.getAttribute('data-code') ? option.getAttribute('data-code').trim().toLowerCase() : '';
                    const text = option.text.toLowerCase();

                    if (code === query || text.includes(query)) {
                        matchedValue = option.value;
                        break;
                    }
                }

                if (matchedValue) {
                    productSelect.value = matchedValue;
                    $('#product_select').val(matchedValue).trigger('change');
                    btnAddItem.click();
                    this.value = '';
                } else {
                    alert(`⚠️ Produk dengan kata kunci/barcode "${this.value}" tidak ditemukan!`);
                }
            }
        });
    }

    // TAMBAH BARANG
    btnAddItem.addEventListener('click', function() {
        const productId = productSelect.value;
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        
        if(!productId || productId === "" || !selectedOption) {
            alert('Silakan pilih produk atau ketik nama/barcode di atas terlebih dahulu!');
            return;
        }

        const regularPrice = parseInt(selectedOption.getAttribute('data-price')) || 0;
        const wholesalePrice = parseInt(selectedOption.getAttribute('data-wholesale-price')) || 0;
        const wholesaleMinQty = parseInt(selectedOption.getAttribute('data-wholesale-qty')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        const type = selectedOption.getAttribute('data-type') || 'single';
        const qty = parseInt(productQty.value) || 1;
        
        let name = selectedOption.text;
        if (name.includes(' (')) {
            name = name.split(' (')[0];
        }

        if(qty <= 0) {
            alert('Jumlah kuantitas minimal 1!');
            return;
        }

        const existingIndex = cartData.findIndex(item => String(item.id) === String(productId) && item.type === type);
        
        if(existingIndex > -1) {
            const totalNewQty = cartData[existingIndex].qty + qty;
            if(type === 'single' && totalNewQty > stock) {
                alert(`Gagal! Stok gudang tidak mencukupi. Sisa stok: ${stock}`);
                return;
            }
            cartData[existingIndex].qty = totalNewQty;
        } else {
            if(type === 'single' && qty > stock) {
                alert(`Gagal! Stok gudang tidak mencukupi. Sisa stok: ${stock}`);
                return;
            }
            cartData.push({ 
                id: String(productId), 
                name: name, 
                regularPrice: regularPrice, 
                wholesalePrice: wholesalePrice,
                wholesaleMinQty: wholesaleMinQty,
                stock: stock,
                qty: qty, 
                type: type 
            });
        }

        productSelect.value = '';
        $('#product_select').val('').trigger('change');
        productQty.value = '1';
        
        renderCart();

        if (barcodeScanner) {
            barcodeScanner.focus();
        }
    });

    function renderCart() {
        cartTableBody.innerHTML = '';
        if(cartData.length === 0) {
            cartTableBody.appendChild(cartEmptyRow);
            updateTotals(0);
            return;
        }

        let totalBelanja = 0;
        cartData.forEach((item, index) => {
            const priceInfo = calculatePrice(item, item.qty);
            const activePrice = priceInfo.price;
            const subtotal = activePrice * item.qty;
            totalBelanja += subtotal;

            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50/50 transition duration-150";
            tr.innerHTML = `
                <td class="p-4 font-bold text-slate-900">
                    ${item.name}
                    ${priceInfo.isWholesale ? '<span class="ml-1 px-1.5 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-md border border-emerald-300">GROSIR</span>' : ''}
                    <input type="hidden" name="products[${index}][id]" value="${item.id}">
                    <input type="hidden" name="products[${index}][type]" value="${item.type}">
                </td>
                <td class="p-4 text-center font-money ${priceInfo.isWholesale ? 'text-emerald-600 font-bold' : 'text-slate-500'}">
                    Rp ${activePrice.toLocaleString('id-ID')}
                </td>
                <td class="p-4 text-center">
                    <input type="number" min="1" value="${item.qty}" data-index="${index}" class="cart-qty-input w-16 bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-center font-money font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                    <input type="hidden" name="products[${index}][quantity]" value="${item.qty}">
                </td>
                <td class="p-4 text-right pr-6 font-money font-bold text-slate-900">Rp ${subtotal.toLocaleString('id-ID')}</td>
                <td class="p-4 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700 font-bold p-1 transition cursor-pointer btn-delete-row" data-id="${item.id}" data-type="${item.type}">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            cartTableBody.appendChild(tr);
        });

        document.querySelectorAll('.cart-qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const idx = parseInt(this.getAttribute('data-index'));
                let newQty = parseInt(this.value) || 1;
                if (newQty < 1) newQty = 1;

                const item = cartData[idx];
                if (item.type === 'single' && newQty > item.stock) {
                    alert(`Gagal! Stok produk tidak mencukupi. Maksimal stok: ${item.stock}`);
                    this.value = item.qty;
                    return;
                }

                cartData[idx].qty = newQty;
                renderCart();
            });
        });

        document.querySelectorAll('.btn-delete-row').forEach(button => {
            button.addEventListener('click', function() {
                const idToDelete = this.getAttribute('data-id');
                const typeToDelete = this.getAttribute('data-type');
                cartData = cartData.filter(item => !(String(item.id) === String(idToDelete) && item.type === typeToDelete));
                renderCart();
            });
        });

        updateTotals(totalBelanja);
    }

    function updateTotals(total = 0) {
        displayTotal.innerText = `Rp ${total.toLocaleString('id-ID')}`;
        inputTotalPrice.value = total;

        // Jika dalam mode QRIS, otomatis set bayar = total
        if (currentPaymentMode === 'qris') {
            payAmountInput.value = total > 0 ? total : 0;
        }

        const payAmount = parseInt(payAmountInput.value) || 0;
        const change = payAmount - total;

        if (total > 0 && payAmount >= total) {
            displayChange.innerText = `Rp ${change.toLocaleString('id-ID')}`;
            displayChange.className = "text-lg font-extrabold font-money text-emerald-600";
            btnSubmit.disabled = false;
            btnSubmit.className = "w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-6 rounded-2xl text-sm tracking-wide shadow-lg shadow-emerald-100 uppercase transition duration-150 cursor-pointer transform hover:-translate-y-0.5";
        } else {
            displayChange.innerText = payAmount > 0 ? "Uang Kurang!" : "Rp 0";
            displayChange.className = payAmount > 0 ? "text-sm font-bold text-rose-600" : "text-lg font-extrabold font-money text-slate-900";
            btnSubmit.disabled = true;
            btnSubmit.className = "w-full bg-slate-300 text-slate-500 font-black py-4 px-6 rounded-2xl text-sm tracking-wide shadow-md uppercase transition duration-150 cursor-not-allowed";
        }
    }

    payAmountInput.addEventListener('input', function() {
        const currentTotal = parseInt(inputTotalPrice.value) || 0;
        updateTotals(currentTotal);
    });
});
</script>
@endsection