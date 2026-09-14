<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm; /* Ukuran standar kertas struk thermal 58mm */
            margin: 0 auto;
            padding: 10px;
            font-size: 11px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .line {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        .badge-payment {
            border: 1px solid #000;
            padding: 2px 4px;
            display: inline-block;
            margin-top: 2px;
            font-size: 10px;
        }
        @media print {
            body { width: 100%; margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 5px 10px; cursor: pointer;">🖨️ Cetak Ulang Nota</button>
    </div>

    <!-- HEADER STRUK -->
    <div class="text-center">
        <h3 style="margin: 0;" class="font-bold uppercase">GROSIR ZHAMZHAM</h3>
        <p style="margin: 2px 0;">Jl. Raya Utama No. 123</p>
        <p style="margin: 2px 0;">Telp: 0812-3456-7890</p>
    </div>

    <div class="line"></div>

    <!-- INFORMASI TRANSAKSI -->
    <div>
        <div>No: {{ $transaction->invoice_number }}</div>
        <div>Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
    </div>

    <div class="line"></div>

    <!-- ITEM BELANJAAN -->
    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
        @foreach($details as $detail)
            <tr>
                <td colspan="3" class="font-bold">{{ $detail->item_name ?? 'Produk' }}</td>
            </tr>
            <tr>
                <td>{{ $detail->quantity }}x @ {{ number_format($detail->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->quantity * $detail->price, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <!-- RINCIAN TOTAL & METODE PEMBAYARAN -->
    <div>
        <div class="flex-between">
            <span>Total Belanja:</span>
            <span class="font-bold">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
        </div>

        <!-- PEMBEDA METODE PEMBAYARAN -->
        <div class="flex-between" style="margin-top: 4px;">
            <span>Metode Bayar:</span>
            <span class="font-bold uppercase">
                @if(strtolower($transaction->payment_method) === 'qris')
                    [ QRIS / NON-TUNAI ]
                @else
                    [ TUNAI ]
                @endif
            </span>
        </div>

        @if(strtolower($transaction->payment_method) === 'qris')
            <!-- TAMPILAN KHUSUS QRIS -->
            <div class="flex-between">
                <span>Status Bayar:</span>
                <span class="font-bold">LUNAS (QRIS)</span>
            </div>
        @else
            <!-- TAMPILAN KHUSUS TUNAI -->
            <div class="flex-between">
                <span>Tunai Diterima:</span>
                <span>Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex-between">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="line"></div>

    <!-- FOOTER STRUK -->
    <div class="text-center" style="margin-top: 8px;">
        @if(strtolower($transaction->payment_method) === 'qris')
            <div class="badge-payment font-bold">PEMBAYARAN DIGITAL QRIS</div>
        @endif
        <p style="margin: 4px 0 0 0;">Terima Kasih Atas Kunjungan Anda!</p>
        <p style="margin: 2px 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>

</body>
</html>