@extends('layouts.layout')

@section('title', 'Stock Movements Audit Log')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Inventory Movements Audit Log</h2>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.movements') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Movement Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sale (-)</option>
                        <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Customer Return (+)</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Stock Purchase (+)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-sm btn-primary">Filter Movements</button>
                    <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Change</th>
                        <th>Stock Before</th>
                        <th>Stock After</th>
                        <th>Staff / User</th>
                        <th>Reference</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>
                                <strong>{{ $m->product->name ?? 'Deleted Product' }}</strong>
                                @if($m->product && $m->product->barcode)
                                    <br><small class="text-muted">{{ $m->product->barcode }}</small>
                                @endif
                            </td>
                            <td>
                                @if($m->type === 'sale')
                                    <span class="badge bg-danger">Sale</span>
                                @elseif($m->type === 'return')
                                    <span class="badge bg-success">Return</span>
                                @elseif($m->type === 'adjustment')
                                    <span class="badge bg-warning text-dark">Adjustment</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ ucfirst($m->type) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($m->quantity > 0)
                                    <strong class="text-success">+{{ $m->quantity }}</strong>
                                @else
                                    <strong class="text-danger">{{ $m->quantity }}</strong>
                                @endif
                            </td>
                            <td>{{ $m->stock_before }}</td>
                            <td><strong>{{ $m->stock_after }}</strong></td>
                            <td>{{ $m->user->name ?? 'System' }}</td>
                            <td>
                                @if($m->reference_type === 'order')
                                    <a href="{{ route('orders.show', $m->reference_id) }}">Order #{{ $m->reference_id }}</a>
                                @else
                                    <small class="text-muted">{{ $m->notes ?: '-' }}</small>
                                @endif
                            </td>
                            <td>{{ $m->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $movements->links('layouts._pagination') }}
    </div>
@endsection
