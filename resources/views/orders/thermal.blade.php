<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            width: 78mm;
            margin: 0 auto;
            padding: 10px 4px;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .store-header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
        }
        .store-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .meta-table td {
            font-size: 11px;
            padding: 2px 0;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
            font-size: 11px;
            padding: 4px 0;
            text-align: left;
        }
        .items-table td {
            font-size: 11px;
            padding: 3px 0;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            border-top: 1px dashed #000;
            margin-top: 6px;
            padding-top: 4px;
        }
        .totals-table td {
            padding: 2px 0;
            font-size: 12px;
        }
        .qr-section {
            text-align: center;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #000;
        }
        .qr-section img {
            width: 110px;
            height: 110px;
            display: inline-block;
        }
        .footer-note {
            text-align: center;
            font-size: 10px;
            margin-top: 8px;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: center;
        }
        .btn-print {
            padding: 6px 14px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: inherit;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨 Print Thermal Receipt</button>
        <a href="{{ route('orders.show', $order->id) }}" style="margin-left: 8px; font-size: 12px;">← Back to Order</a>
    </div>

    <div class="store-header">
        <div class="store-title">🛍️ POS SYSTEM</div>
        <small>Smart Sales Terminal</small><br>
        <small>Tax / VAT Reg: 300123456789</small>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Receipt #:</strong> {{ $order->id }}</td>
            <td class="text-end"><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Cashier:</strong> {{ $order->user->name ?? 'Staff' }}</td>
            <td class="text-end"><strong>Shift #:</strong> {{ $order->shift_id ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Customer:</strong> {{ $order->customer->name ?? 'Walk-in Customer' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Price</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">${{ number_format($item->price_per_unit, 2) }}</td>
                    <td class="text-end">${{ number_format($item->quantity * $item->price_per_unit, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-end">${{ number_format($order->subtotal ?? $order->total_price, 2) }}</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td>Discount:</td>
            <td class="text-end">-${{ number_format($order->discount, 2) }}</td>
        </tr>
        @endif
        <tr class="fw-bold" style="font-size: 14px;">
            <td>TOTAL:</td>
            <td class="text-end">${{ number_format($order->total_price, 2) }}</td>
        </tr>
        <tr>
            <td>Payment ({{ strtoupper($order->payment_method ?? 'CASH') }}):</td>
            <td class="text-end">${{ number_format($order->paid_amount ?? $order->total_price, 2) }}</td>
        </tr>
        @if($order->payment_method === 'cash')
        <tr>
            <td>Change Due:</td>
            <td class="text-end">${{ number_format($order->change_amount ?? 0, 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="qr-section">
        <img src="{{ $qrUrl }}" alt="Receipt QR">
        <div style="font-size: 10px; margin-top: 4px;">Scan to verify e-Invoice</div>
    </div>

    <div class="footer-note">
        <p>Thank you for your business!<br>Please retain this receipt for any returns.</p>
    </div>

    <script>
        // Auto trigger print when opened if user wants
        // window.onload = () => { window.print(); };
    </script>
</body>
</html>
