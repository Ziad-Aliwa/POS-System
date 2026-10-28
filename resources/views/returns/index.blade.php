@extends('layouts.layout')

@section('title', 'Processed Returns & Refunds')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🔄 Processed Returns & Refunds</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Return #</th>
                        <th>Original Order</th>
                        <th>Customer</th>
                        <th>Processed By</th>
                        <th>Total Refund</th>
                        <th>Refund Method</th>
                        <th>Items Returned</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td><strong>#{{ $return->id }}</strong></td>
                            <td>
                                <a href="{{ route('orders.show', $return->order_id) }}">
                                    Order #{{ $return->order_id }}
                                </a>
                            </td>
                            <td>{{ $return->order->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ $return->user->name ?? 'Staff' }}</td>
                            <td><strong class="text-danger">-${{ number_format($return->total_refund, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $return->refund_method }}</span>
                            </td>
                            <td>
                                @foreach($return->items as $item)
                                    <div>{{ $item->product->name }} <span class="badge bg-light text-dark">×{{ $item->quantity }}</span></div>
                                @endforeach
                            </td>
                            <td><small class="text-muted">{{ $return->reason ?: 'No reason specified' }}</small></td>
                            <td>{{ $return->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No returns processed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $returns->links('layouts._pagination') }}
    </div>
@endsection
