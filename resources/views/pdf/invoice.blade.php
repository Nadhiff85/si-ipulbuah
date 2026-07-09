<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #2C2C2C; }
        h1 { color: #2E7D32; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border-bottom: 1px solid #eee; padding: 6px; text-align: left; }
        .total-row td { font-weight: bold; border-top: 2px solid #2E7D32; }
    </style>
</head>
<body>
    <h1>IPUL BUAH</h1>
    <p>Invoice: {{ $order->order_number }}</p>
    <p>Pelanggan: {{ $order->user->name }} ({{ $order->user->phone }})</p>
    <p>Tanggal: {{ $order->created_at->format('d F Y H:i') }}</p>
    <p>Metode: {{ $order->fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}</p>

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td colspan="3">Ongkos Kirim</td><td>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td></tr>
            <tr><td colspan="3">Diskon</td><td>- Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td colspan="3">Total</td><td>Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
