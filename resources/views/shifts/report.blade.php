@extends('layouts.layout')

@section('title', 'Z-Report - Shift #' . $shift->id)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🧾 Shift #{{ $shift->id }} End-of-Day Z-Report</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">🖨 Print Z-Report</button>
            <a href="{{ route('shifts.index') }}" class="btn btn-secondary">← Back to Shifts</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">Shift & Cashier Info</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Shift Status:</strong> 
                        <span class="badge {{ $shift->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                            {{ strtoupper($shift->status) }}
                        </span>
                    </p>
                    <p class="mb-2"><strong>Cashier:</strong> {{ $shift->user->name ?? 'Staff' }} ({{ $shift->user->email ?? '-' }})</p>
                    <p class="mb-2"><strong>Opened At:</strong> {{ $shift->opened_at ? $shift->opened_at->format('Y-m-d H:i:s') : '-' }}</p>
                    <p class="mb-2"><strong>Closed At:</strong> {{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i:s') : 'Still Open' }}</p>
                    @if($shift->notes)
                        <hr>
                        <p class="mb-0 text-muted"><strong>Notes:</strong> {{ $shift->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">Cash Reconciliation</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Opening Cash (Float):</span>
                        <strong>${{ number_format($shift->opening_cash, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>+ Total Cash Sales:</span>
                        <strong>+${{ number_format($shift->cash_sales, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-2 fs-6">
                        <span>= Expected Cash in Drawer:</span>
                        <strong>${{ number_format($shift->expected_cash ?? ($shift->opening_cash + $shift->cash_sales), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-6">
                        <span>Actual Counted Cash:</span>
                        <strong class="text-primary">
                            {{ $shift->closing_cash !== null ? '$' . number_format($shift->closing_cash, 2) : 'Not counted yet' }}
                        </strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>Discrepancy (Over / Short):</span>
                        @if($shift->closing_cash !== null)
                            @if($diff == 0)
                                <span class="text-success">$0.00 (Balanced)</span>
                            @elseif($diff > 0)
                                <span class="text-info">+${{ number_format($diff, 2) }} (Over)</span>
                            @else
                                <span class="text-danger">-${{ number_format(abs($diff), 2) }} (Shortage)</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Breakdown by Payment Method --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm p-3">
                <small class="text-white-50">Cash Sales</small>
                <h4>${{ number_format($shift->cash_sales, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm p-3">
                <small class="text-white-50">Card Sales</small>
                <h4>${{ number_format($shift->card_sales, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm p-3">
                <small class="text-white-50">Transfer Sales</small>
                <h4>${{ number_format($shift->transfer_sales, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow-sm p-3">
                <small class="text-white-50">Total Shift Revenue</small>
                <h4>${{ number_format($shift->cash_sales + $shift->card_sales + $shift->transfer_sales, 2) }}</h4>
            </div>
        </div>
    </div>

    {{-- Orders completed during this shift --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">Orders Processed in Shift ({{ $shift->orders->count() }})</div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shift->orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->customer->name ?? 'Walk-in' }}</td>
                            <td><span class="badge bg-secondary text-uppercase">{{ $order->payment_method }}</span></td>
                            <td>${{ number_format($order->discount ?? 0, 2) }}</td>
                            <td><strong>${{ number_format($order->total_price, 2) }}</strong></td>
                            <td>{{ $order->created_at->format('H:i:s') }}</td>
                            <td>
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">No orders in this shift.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
