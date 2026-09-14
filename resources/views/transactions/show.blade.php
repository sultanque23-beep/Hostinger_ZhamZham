<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk_{{ $transaction->invoice_number }}</title>
    <style>
        /* REGISTER FONT CLEAN & MONOSPACE UNTUK STRUK */
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Plus+Jakarta+Sans:wght@400;700&display=swap');
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Courier Prime', monospace; /* Font kasir klasik agar text sejajar sempurna */
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.4;
            padding: 10px;
        }

        /* UKURAN STRUK THERMAL (Lebar standar kertas kasir 58mm - 80mm) */
        .ticket {
            max-width: 300px; /* Lebar maksimal ideal di layar & kertas thermal */
            margin: 0 auto;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        /* HEADER TOKO */
        .header {
            margin-bottom: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .header .brand {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }

        /* METADATA NOTA */
        .meta-info {
            font-size: 11px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        /* TABEL RINCIAN BELANJAAN */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
        }
        
        td {
            padding: 5px 0;
            vertical-align: top;
        }

        .product-name {
            display: block;
            font-weight: bold;
        }

        /* TOTALAN & KALKULATOR */
        .totals-section {
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-bottom: 15px;
        }
        
        .flex-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }

        /* FOOTER / TERIMA KASIH */
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        /* TOMBOL CETAK LAYAR (OTOMATIS HILANG SAAT DI-PRINT) */
        .no-print-zone {
            background: #f1f5f9;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .btn-print {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        
        .btn-print:hover { background: #047857; }

        /* TRIGGER CSS KETIKA TOMBOL PRINT DIKLIK / DIALIKHAN KE PRINTER */
        @media print {
            .no-print-zone {
                display: none !important; /* Hilangkan tombol cetak di kertas */
            }
            body {
                padding: 0;
                margin: 0;
            }
            .ticket {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-zone">
        <button class="btn-print" onclick="window.print()">
            🖨️ Klik Untuk Cetak Struk Fisik
        </button>
        <p style="font-size: 11px; color: #64748b; margin-top: 8px;">
            Atau tekan <kbd style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px;">Ctrl + P</kbd> pada keyboard kasir
        </p>
    </div>

    <div class="ticket">
        
        <div class="header text-center">
            <div class="brand">Grosir ZhamZham</div>
            <div class="subtitle">Pusat Sembako & Kebutuhan Pokok</div>
            <div class="subtitle">Jl. Raya Toko ZhamZham - Indonesia</div>
        </div>

        <div class="meta-info">
            <div class="flex-row">
                <span>Nota: {{ $transaction->invoice_number }}</span>
                <span>Kasir Utama</span>
            </div>
            <div class="flex-row">
                <span>Tgl : {{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                <span>Status: LUNAS</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item / Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details ?? $details ?? [] as $detail)
                <tr>
                    <td>
                        <span class="product-name">{{ $detail->product->name ?? 'Produk Sembako' }}</span>
                        <span>{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-right" style="vertical-align: bottom;">
                        Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="flex-row bold">
                <span>TOTAL AKHIR:</span>
                <span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex-row">
                <span>TUNAI/BAYAR:</span>
                <span>Rp {{ number_format($transaction->pay_amount ?? $transaction->total_price, 0, ',', '.') }}</span>
            </div>
           @php
                $pay = $transaction->pay_amount ?? $transaction->total_price;
                $total = $transaction->total_price;
                $change = $pay - $total;
            @endphp
            <div class="flex-row">
                <span>KEMBALIAN:</span>
                <span>Rp {{ number_format($change >= 0 ? $change : 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer text-center">
            <p class="bold">TERIMA KASIH</p>
            <p>Sudah Berbelanja di Grosir ZhamZham</p>
            <p style="margin-top: 5px; font-size: 8px; font-style: italic;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        </div>

    </div>

</body>
</html>