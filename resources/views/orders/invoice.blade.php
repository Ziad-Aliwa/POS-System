<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .customer-info {
            margin-bottom: 20px;
        }

        .customer-info strong {
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        table thead {
            background-color: #f5f5f5;
        }

        .total {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <h2>Invoice #{{ $order->id }}</h2>

        <div class="customer-info">
            <table style="border: none; margin-bottom: 15px;">
                <tr style="border: none;">
                    <td style="border: none; padding: 4px; width: 50%;">
                        <strong>Customer:</strong> {{ $order->customer->name ?? 'Walk-in Customer' }}<br>
                        <strong>Phone:</strong> {{ $order->customer->phone ?? 'N/A' }}<br>
                        <strong>Email:</strong> {{ $order->customer->email ?? 'N/A' }}
                    </td>
                    <td style="border: none; padding: 4px; width: 50%; text-align: right;">
                        <strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}<br>
                        <strong>Cashier:</strong> {{ $order->user->name ?? 'N/A' }}<br>
                        <strong>Payment:</strong> {{ strtoupper($order->payment_method ?? 'CASH') }}
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">${{ number_format($item->price_per_unit, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item->price_per_unit * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="total">Subtotal</td>
                    <td class="total" style="text-align: right;">${{ number_format($order->subtotal ?? $order->total_price, 2) }}</td>
                </tr>
                @if($order->discount > 0)
                <tr>
                    <td colspan="3" class="total" style="color: #c00;">Discount</td>
                    <td class="total" style="text-align: right; color: #c00;">-${{ number_format($order->discount, 2) }}</td>
                </tr>
                @endif
                <tr style="background-color: #f9f9f9;">
                    <td colspan="3" class="total" style="font-size: 16px;">Final Total</td>
                    <td class="total" style="text-align: right; font-size: 16px;">${{ number_format($order->total_price, 2) }}</td>
                </tr>
                @if($order->payment_method === 'cash')
                <tr>
                    <td colspan="3" class="total">Amount Tendered</td>
                    <td class="total" style="text-align: right;">${{ number_format($order->paid_amount ?? $order->total_price, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="total">Change Due</td>
                    <td class="total" style="text-align: right;">${{ number_format($order->change_amount ?? 0, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your purchase!</p>
        </div>
    </div>
</body>

</html>
