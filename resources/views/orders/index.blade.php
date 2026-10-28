@extends('layouts.layout')

@section('title', 'Orders')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Orders & Sales History</h2>
        <a href="{{ route('orders.export', request()->query()) }}" class="btn btn-success">
            📊 Export to Excel (CSV)
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Customer Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search customer...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Payment Method</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="ordersTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Payment</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Date</th>
                    @if (in_array(auth()->user()->role, ['admin', 'cashier']))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>
                            <strong>{{ $order->customer->name ?? '-' }}</strong>
                            @if($order->customer && $order->customer->phone)
                                <br><small class="text-muted">{{ $order->customer->phone }}</small>
                            @endif
                        </td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>
                            @if($order->payment_method === 'cash')
                                <span class="badge bg-success">💵 Cash</span>
                            @elseif($order->payment_method === 'card')
                                <span class="badge bg-primary">💳 Card</span>
                            @elseif($order->payment_method === 'transfer')
                                <span class="badge bg-info text-dark">🏦 Transfer</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_method ?? 'Cash') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($order->discount > 0)
                                <span class="text-danger">-${{ number_format($order->discount, 2) }}</span>
                            @else
                                <span class="text-muted">$0.00</span>
                            @endif
                        </td>
                        <td><strong class="text-primary">${{ number_format($order->total_price, 2) }}</strong></td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="d-flex justify-content-start gap-2">
                                @if (auth()->user()->role === 'admin')
                                    <button class="btn btn-sm btn-danger w-auto" data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal" data-id="{{ $order->id }}"
                                        data-customer="{{ $order->customer->name ?? 'N/A' }}"
                                        data-total="{{ number_format($order->total_price, 2) }}">
                                        Delete
                                    </button>
                                @endif

                                <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="btn btn-sm btn-primary w-auto">
                                    Print PDF
                                </a>

                                <a href="{{ route('orders.show', $order->id) }}"
                                    class="btn btn-sm btn-info text-white w-auto">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="deleteOrderForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this order?</p>
                            <p><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                            <p><strong>Total:</strong> $<span id="modalOrderTotal"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-center">
        {{ $orders->appends(request()->query())->links('layouts._pagination') }}
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#ordersTable tbody tr');

        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                const customerName = row.children[1].textContent.toLowerCase();
                row.style.display = customerName.includes(searchTerm) ? '' : 'none';
            });
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmDeleteModal');
        const customerSpan = document.getElementById('modalCustomerName');
        const totalSpan = document.getElementById('modalOrderTotal');
        const form = document.getElementById('deleteOrderForm');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-id');
            const customer = button.getAttribute('data-customer');
            const total = button.getAttribute('data-total');

            customerSpan.textContent = customer;
            totalSpan.textContent = total;

            form.action = `{{ route('orders.destroy', ['order' => '__ID__']) }}`.replace('__ID__',
                orderId);
        });
    });
</script>



{{-- @extends('layouts.layout')

@section('title', 'Orders')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Orders Management</h2>
            <div class="input-group input-group-sm w-25">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by customer name...">
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Date</th>
                            @if (in_array(auth()->user()->role, ['admin', 'cashier']))
                                <th class="text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-4">{{ $order->id }}</td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td class="text-success fw-medium">${{ number_format($order->total_price, 2) }}</td>
                                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                @if (in_array(auth()->user()->role, ['admin', 'cashier']))
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if (auth()->user()->role === 'admin')
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmDeleteModal" 
                                                        data-id="{{ $order->id }}"
                                                        data-customer="{{ $order->customer->name }}"
                                                        data-total="{{ number_format($order->total_price, 2) }}">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            @endif
                                            <form action="{{ route('orders.print', $order->id) }}" method="GET" target="_blank">
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-printer"></i> Print
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ in_array(auth()->user()->role, ['admin', 'cashier']) ? 5 : 4 }}" 
                                    class="text-center text-muted py-4">
                                    <i class="bi bi-inbox me-2"></i>No orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="deleteOrderForm">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to delete this order?</p>
                        <div class="alert alert-warning py-2">
                            <p class="mb-1"><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                            <p class="mb-0"><strong>Total:</strong> $<span id="modalOrderTotal"></span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table th, .table td {
        vertical-align: middle;
        padding: 12px;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .card {
        border: none;
        border-radius: 12px;
    }
    
    .card-header {
        border-radius: 12px 12px 0 0;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .pagination {
        --bs-pagination-border-radius: 8px;
    }
    
    @media (max-width: 576px) {
        .input-group.w-25 {
            width: 100% !important;
            margin-top: 1rem;
        }
        
        .table {
            font-size: 0.875rem;
        }
        
        .btn-sm {
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const input = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#ordersTable tbody tr');

    input.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        tableRows.forEach(row => {
            const customerName = row.children[1].textContent.toLowerCase();
            row.style.display = customerName.includes(searchTerm) ? '' : 'none';
        });
    });

    // Delete modal functionality
    const modal = document.getElementById('confirmDeleteModal');
    const customerSpan = document.getElementById('modalCustomerName');
    const totalSpan = document.getElementById('modalOrderTotal');
    const form = document.getElementById('deleteOrderForm');

    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-id');
        const customer = button.getAttribute('data-customer');
        const total = button.getAttribute('data-total');

        customerSpan.textContent = customer;
        totalSpan.textContent = total;
        form.action = `{{ route('orders.destroy', ['order' => '__ID__']) }}`.replace('__ID__', orderId);
    });
});
</script>
@endsection --}}
