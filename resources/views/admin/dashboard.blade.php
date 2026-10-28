@extends('layouts.layout')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📊 Admin Executive Dashboard</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('orders.export') }}" class="btn btn-outline-success">
                📥 Export Sales (CSV)
            </a>
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-primary">
                ⏱️ Shifts & Z-Reports
            </a>
            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-dark">
                📦 Stock Audit Log
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                + Add Product
            </a>
        </div>
    </div>

    {{-- Financial KPIs Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <span class="text-white-50">Total Revenue</span>
                    <h3 class="mt-1">${{ number_format($totalRevenue, 2) }}</h3>
                    <small>All time revenue</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <span class="text-white-50">Estimated Net Profit</span>
                    <h3 class="mt-1">+${{ number_format($totalProfit, 2) }}</h3>
                    <small>Today's profit: +${{ number_format($todayProfit, 2) }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body">
                    <span class="text-white-50">Discounts & Returns</span>
                    <h3 class="mt-1">-${{ number_format($totalDiscount + $totalRefunded, 2) }}</h3>
                    <small>Discounts: ${{ number_format($totalDiscount, 2) }} | Refunds: ${{ number_format($totalRefunded, 2) }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm h-100">
                <div class="card-body">
                    <span class="text-white-50">Total Orders</span>
                    <h3 class="mt-1">{{ $ordersCount }}</h3>
                    <small>{{ $cashOrdersCount }} Cash | {{ $cardOrdersCount }} Card</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock Alerts if any --}}
    @if($lowStockProducts && $lowStockProducts->count())
        <div class="alert alert-warning border-warning shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="alert-heading mb-0">⚠️ Low Stock Warning ({{ $lowStockProducts->count() }} items need restock)</h5>
                <a href="{{ route('products.all_products') }}" class="btn btn-sm btn-outline-dark">Manage Inventory</a>
            </div>
            <div class="row g-2 mt-1">
                @foreach($lowStockProducts as $lowProd)
                    <div class="col-md-3 col-sm-6">
                        <div class="bg-white p-2 rounded border border-warning d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block text-truncate" style="max-width: 150px;">{{ $lowProd->name }}</strong>
                                <small class="text-muted">Min: {{ $lowProd->alert_quantity }}</small>
                            </div>
                            <span class="badge {{ $lowProd->stock_quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }} fs-6">
                                {{ $lowProd->stock_quantity }} left
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Secondary Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <h6 class="text-muted mb-1">Total Products</h6>
                <h4 class="text-dark">{{ $productsCount }}</h4>
                <a href="{{ route('products.all_products') }}" class="small text-decoration-none">Manage products →</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <h6 class="text-muted mb-1">Registered Customers</h6>
                <h4 class="text-dark">{{ $customersCount }}</h4>
                <a href="{{ route('customers.index') }}" class="small text-decoration-none">Manage customers →</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <h6 class="text-muted mb-1">Processed Returns</h6>
                <h4 class="text-dark">{{ $totalReturnsCount }}</h4>
                <a href="{{ route('returns.index') }}" class="small text-decoration-none">View returns log →</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <h6 class="text-muted mb-1">Active Shifts</h6>
                <h4 class="text-dark">{{ $activeShifts->count() }} Open</h4>
                <a href="{{ route('shifts.index') }}" class="small text-decoration-none">View shifts & Z-Reports →</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Latest Orders --}}
        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Recent Sales Transactions</span>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">All Orders</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Method</th>
                                <th>Total</th>
                                <th>Net Profit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->customer->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase">{{ $order->payment_method }}</span>
                                    </td>
                                    <td><strong>${{ number_format($order->total_price, 2) }}</strong></td>
                                    <td class="text-success fw-bold">+${{ number_format($order->profit ?? 0, 2) }}</td>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-info">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No recent orders</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Stock Movements --}}
        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Live Stock Activity</span>
                    <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-dark">Audit Log</a>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $move)
                                <tr>
                                    <td class="fw-semibold text-truncate" style="max-width: 140px;">
                                        {{ $move->product->name ?? 'Product' }}
                                    </td>
                                    <td>
                                        @if($move->type === 'sale')
                                            <span class="badge bg-danger">Sale</span>
                                        @elseif($move->type === 'return')
                                            <span class="badge bg-success">Return</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ ucfirst($move->type) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($move->quantity > 0)
                                            <span class="text-success fw-bold">+{{ $move->quantity }}</span>
                                        @else
                                            <span class="text-danger fw-bold">{{ $move->quantity }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $move->stock_after }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No stock movements yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@endsection
