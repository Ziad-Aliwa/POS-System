@extends('layouts.layout')

@section('title', 'Process Return for Order #' . $order->id)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🔄 Process Return for Order #{{ $order->id }}</h2>
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">← Back to Order</a>
    </div>

    <form action="{{ route('orders.return.store', $order->id) }}" method="POST">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-bold">Select Items & Quantities to Return</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Original Qty</th>
                            <th>Unit Price</th>
                            <th>Already Returned</th>
                            <th>Available to Return</th>
                            <th style="width: 160px;">Return Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $index => $item)
                            @php
                                $alreadyReturned = $item->product->orderItems
                                    ? \App\Models\OrderReturnItem::where('order_item_id', $item->id)->sum('quantity')
                                    : 0;
                                $maxReturn = max(0, $item->quantity - $alreadyReturned);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    @if($item->product->barcode)
                                        <br><small class="text-muted">Barcode: {{ $item->product->barcode }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->price_per_unit, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ $alreadyReturned }}</span></td>
                                <td>
                                    @if($maxReturn > 0)
                                        <span class="badge bg-success">{{ $maxReturn }}</span>
                                    @else
                                        <span class="badge bg-danger">0 (All Returned)</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                    <input type="number" name="items[{{ $index }}][quantity]" 
                                           class="form-control form-control-sm text-center return-qty-input"
                                           value="0" min="0" max="{{ $maxReturn }}" 
                                           {{ $maxReturn === 0 ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">Refund Method & Reason</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Refund Payment Method:</label>
                        <select name="refund_method" class="form-select" required>
                            <option value="cash" {{ $order->payment_method === 'cash' ? 'selected' : '' }}>💵 Cash Refund</option>
                            <option value="card" {{ $order->payment_method === 'card' ? 'selected' : '' }}>💳 Card Reversal</option>
                            <option value="transfer" {{ $order->payment_method === 'transfer' ? 'selected' : '' }}>🏦 Bank Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reason for Return (Optional):</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Defective item, customer changed mind">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted">Stock will be automatically restocked upon processing.</span>
                <button type="submit" class="btn btn-danger px-4" onclick="return confirm('Are you sure you want to process this return and restock items?')">
                    ✔ Confirm & Process Refund
                </button>
            </div>
        </div>
    </form>
@endsection
