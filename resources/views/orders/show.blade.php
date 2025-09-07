@extends('layouts.layout')

@section('title', 'Order Details')

@section('content')
    <div class="container">
        <h2 class="mb-4">Order #{{ $order->id }} Details</h2>

        <p><strong>Customer:</strong> {{ $order->customer->name ?? '-' }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Total:</strong> ${{ number_format($order->total_price, 2) }}</p>

        <hr>

        <h5>Items</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->product->price, 2) }}</td>
                        <td>${{ number_format($item->quantity * $item->product->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
