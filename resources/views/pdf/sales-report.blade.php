<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #2C2C2C; }
        h1 { color: #2E7D32; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border-bottom: 1px solid #eee; padding: 6px; text-align: left; }
        .total-row td { font-weight: bold; border-top: 2px solid #2E7D32; }
    </style>
</head>
<body>
    <h1>IPUL BUAH - Laporan Penjualan</h1>
    <p>Periode: {{ $from ?? 'Semua' }} s/d {{ $to ?? 'Semua' }}</p>

    <table>
        <thead><tr><th>No. Invoice</th><th>Tanggal</th><th>Tipe</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($order->fulfillment_type) }}</td>
                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row"><td colspan="3">Total Keseluruhan</td><td>Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
