<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Nota Harian - Grosir ZhamZham</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; color: #000; margin: 20px; }
        .nota-wrapper { width: 300px; margin: 0 auto 50px auto; padding-bottom: 20px; border-bottom: 2px dashed #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 4px 0; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        
        /* Efek sakti: Memisahkan halaman kertas otomatis saat di-print ke printer harian */
        @media print {
            body { margin: 0; }
            .nota-wrapper { page-break-after: always; border-bottom: none; margin: 0 auto; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 30px;">
        <button onclick="window.print()" style="background: #059669; color: white; padding: 10px 20px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">🖨️ Mulai Cetak Masal</button>
        <a href="{{ route('products.index') }}" style="background: #4b5563; color: white; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 5px;">🏠 Kembali</a>
    </div>

    @foreach($transactions as $transaction)
    <div class="nota-wrapper">
        <div class="text-center">
            <h3 style="margin: 0; font-size: 16px;">GROSIR ZHAMZHAM</h3>
            <p style="margin: 2px 0; font-size: 12px;">Sembako & Kebutuhan Pokok</p>
            <p style="margin: 2px 0; font-size: 11px;">Nota: {{ $transaction->invoice_number }}</p>
            <p style="margin: 2px 0; font-size: 11px;">Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        <div class="line"></div>
        
        <table>
            <thead>
                <tr style="text-align: left; font-size: 12px;">
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                <tr style="font-size: 12px;">
                    <td>{{ $detail->product->name ?? 'Produk Terhapus' }}</td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="line"></div>
        
        <table style="font-weight: bold; font-size: 13px;">
            <tr>
                <td>TOTAL AKHIR:</td>
                <td class="text-right">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: normal; font-size: 12px;">
                <td>Tunai:</td>
                <td class="text-right">Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: normal; font-size: 12px;">
                <td>Kembalian:</td>
                <td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="line"></div>
        <p class="text-center" style="font-size: 11px; margin: 5px 0 0 0;">-- Terima Kasih --</p>
    </div>
    @endforeach

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Hilangkan komentar di bawah jika ingin langsung print otomatis saat diklik
            // window.print();
        });
    </script>
</body>
</html>