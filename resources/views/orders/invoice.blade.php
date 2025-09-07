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
            <p><strong>Customer:</strong> {{ $order->customer->name ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $order->customer->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $order->customer->phone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $order->customer->address ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>${{ number_format($item->product->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->product->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="total">Total</td>
                    <td class="total">${{ number_format($order->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your purchase!</p>
        </div>
    </div>
</body>

</html>
