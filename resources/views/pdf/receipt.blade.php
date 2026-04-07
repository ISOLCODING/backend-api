<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $transaction->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.2;
            margin: 5mm;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-uppercase { text-transform: uppercase; }
        .font-bold { font-weight: bold; }
        
        .header {
            margin-bottom: 5mm;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .store-info {
            font-size: 10px;
            margin-bottom: 10px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table td {
            padding: 2px 0;
        }
        
        .summary-table {
            margin-top: 5px;
        }
        .summary-table td {
            padding: 1px 0;
        }
        
        .footer {
            margin-top: 15px;
            font-size: 9px;
        }
        .receipt-id {
            margin-top: 5px;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="store-name">{{ $settings->store_name ?? 'KASIRIN AJA' }}</div>
        <div class="store-info">
            {{ $settings->store_address ?? 'Alamat Toko Belum Diatur' }}<br>
            Telp: {{ $settings->store_phone ?? '-' }}
        </div>
        <div class="divider"></div>
        <div class="font-bold">STRUK PEMBAYARAN</div>
        <div>{{ $transaction->created_at->format('d/m/Y H:i') }} | #{{ $transaction->invoice_number }}</div>
        <div>Kasir: {{ $transaction->user->name }}</div>
    </div>

    <div class="divider"></div>

    <table class="items-table">
        <tbody>
            @foreach($transaction->details as $detail)
            <tr>
                <td colspan="3">{{ $detail->product_name }}</td>
            </tr>
            <tr>
                <td width="40%">{{ $detail->quantity }} x {{ number_format($detail->sell_price, 0, ',', '.') }}</td>
                <td class="text-right" width="60%">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="summary-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount_amount > 0)
        <tr>
            <td>Diskon:</td>
            <td class="text-right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td>PPN ({{ $tax_rate }}%):</td>
            <td class="text-right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="font-bold">
            <td style="font-size: 13px;">TOTAL:</td>
            <td class="text-right" style="font-size: 13px;">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="2"><div class="divider"></div></td></tr>
        <tr>
            <td>Metode Bayar:</td>
            <td class="text-right text-uppercase">{{ $transaction->payment_method }}</td>
        </tr>
        <tr>
            <td>Bayar:</td>
            <td class="text-right">Rp {{ number_format($transaction->paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali:</td>
            <td class="text-right">Rp {{ number_format($transaction->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer text-center">
        <div class="divider"></div>
        <div class="font-bold">TERIMA KASIH</div>
        <div>Silakan berkunjung kembali!</div>
        <div class="receipt-id">Powered by Kasirin Aja - Solusi Kasir Pintar</div>
    </div>
</body>
</html>
