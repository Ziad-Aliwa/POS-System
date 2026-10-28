@extends('layouts.layout')

@section('title', 'Order #' . $order->id . ' Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #{{ $order->id }} Details</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('orders.thermal', $order->id) }}" target="_blank" class="btn btn-outline-dark">
                🧾 80mm Thermal Receipt
            </a>
            <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="btn btn-primary">
                🖨 Print Invoice PDF
            </a>
            <a href="{{ route('orders.return.create', $order->id) }}" class="btn btn-warning">
                🔄 Process Return
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                ← Back
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Customer & Cashier Info</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Customer:</strong> {{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $order->customer->phone ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->customer->email ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Cashier:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Shift:</strong> {{ $order->shift_id ? '#' . $order->shift_id : 'No shift attached' }}</p>
                    <p class="mb-0"><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Payment & Status Summary</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Payment Method:</strong> 
                        <span class="badge bg-primary text-uppercase">{{ $order->payment_method ?? 'CASH' }}</span>
                    </p>
                    <p class="mb-1"><strong>Status:</strong> 
                        <span class="badge bg-success">{{ ucfirst($order->status ?? 'Completed') }}</span>
                    </p>
                    <p class="mb-1"><strong>Subtotal:</strong> ${{ number_format($order->subtotal ?? $order->total_price, 2) }}</p>
                    <p class="mb-1"><strong>Discount:</strong> <span class="text-danger">-${{ number_format($order->discount ?? 0, 2) }}</span></p>
                    <p class="mb-1 fs-5"><strong>Total Paid:</strong> <strong class="text-primary">${{ number_format($order->total_price, 2) }}</strong></p>
                    @if(auth()->user()->role === 'admin')
                        <p class="mb-1 text-success"><strong>Estimated Net Profit:</strong> +${{ number_format($order->profit ?? 0, 2) }}</p>
                    @endif
                    @if($order->payment_method === 'cash')
                        <p class="mb-1"><strong>Amount Tendered:</strong> ${{ number_format($order->paid_amount ?? $order->total_price, 2) }}</p>
                        <p class="mb-0"><strong>Change / الباقي:</strong> <span class="text-success fw-bold">${{ number_format($order->change_amount ?? 0, 2) }}</span></p>
                    @endif
                    @if($order->notes)
                        <hr class="my-2">
                        <p class="mb-0 text-muted"><strong>Notes:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($order->returns && $order->returns->count())
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold">Returns Recorded for this Order</div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Refund Amount</th>
                            <th>Method</th>
                            <th>Items</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->returns as $ret)
                            <tr>
                                <td>#{{ $ret->id }}</td>
                                <td class="text-danger fw-bold">-${{ number_format($ret->total_refund, 2) }}</td>
                                <td>{{ ucfirst($ret->refund_method) }}</td>
                                <td>
                                    @foreach($ret->items as $retItem)
                                        <span class="badge bg-secondary">{{ $retItem->product->name }} (×{{ $retItem->quantity }})</span>
                                    @endforeach
                                </td>
                                <td>{{ $ret->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">Ordered Items</div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Barcode</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        @if(auth()->user()->role === 'admin')
                            <th>Unit Cost</th>
                        @endif
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->product->name }}</td>
                            <td><span class="badge bg-secondary">{{ $item->product->barcode ?? '-' }}</span></td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price_per_unit, 2) }}</td>
                            @if(auth()->user()->role === 'admin')
                                <td>${{ number_format($item->cost_price ?? 0, 2) }}</td>
                            @endif
                            <td>${{ number_format($item->quantity * $item->price_per_unit, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@endsection
