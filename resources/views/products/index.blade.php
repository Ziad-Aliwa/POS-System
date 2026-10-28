@extends('layouts.layout')

@section('title', 'Smart POS Terminal')

@section('content')
<div class="container-fluid px-3">

    {{-- Top Terminal Bar: Shift Status & Quick Tools --}}
    @php
        $activeShift = \App\Models\Shift::currentForUser(auth()->id());
    @endphp

    <div class="card shadow-sm border-0 mb-3 bg-white">
        <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-5 fw-bold text-dark">⚡ Point of Sale Terminal</span>
                @if($activeShift)
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                        🟢 Active Shift #{{ $activeShift->id }} (Opened: ${{ number_format($activeShift->opening_cash, 2) }})
                    </span>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                        Close Shift / Z-Report
                    </button>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                        ⚠️ No Active Shift
                    </span>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                        + Start Shift (Enter Float)
                    </button>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#heldOrdersModal" id="heldOrdersBtn">
                    ⏸ Parked / Held Orders <span class="badge bg-danger rounded-pill" id="heldOrdersCount">0</span>
                </button>
                <span class="text-muted small d-none d-md-inline">Shortcuts: <strong>F2</strong> Search | <strong>F3</strong> Barcode | <strong>F4</strong> Pay</span>
            </div>
        </div>
    </div>

    {{-- Split POS Interface --}}
    <div class="row g-3">

        {{-- LEFT COLUMN: Catalog, Barcode Scanner & Category Pills (65%) --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">

                    {{-- Barcode Scanner & Live Search Header --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white fw-bold">📷 Barcode (F3)</span>
                                <input type="text" id="barcodeInput" class="form-control" 
                                       placeholder="Scan barcode (e.g. 10001) & press Enter" autofocus>
                                <button class="btn btn-dark" type="button" id="barcodeBtn">Scan</button>
                            </div>
                            <div id="barcodeFeedback" class="small mt-1" style="min-height: 18px;"></div>
                        </div>

                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-secondary text-white">🔍 Search (F2)</span>
                                <input type="text" id="searchInput" class="form-control" 
                                       placeholder="Search product name, category, or barcode...">
                            </div>
                        </div>
                    </div>

                    {{-- Category Quick Filters Pills --}}
                    <div class="d-flex gap-2 overflow-auto pb-2 mb-3 border-bottom" id="categoryTabs">
                        <a href="{{ route('products.index') }}" 
                           class="btn btn-sm {{ !request('category') || request('category') === 'all' ? 'btn-dark' : 'btn-outline-secondary' }} text-nowrap rounded-pill px-3">
                            All Categories
                        </a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('products.index', ['category' => $cat]) }}" 
                               class="btn btn-sm {{ request('category') === $cat ? 'btn-dark' : 'btn-outline-secondary' }} text-nowrap rounded-pill px-3">
                                {{ $cat }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Products Grid --}}
                    <div class="row g-2" id="productsGrid" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                        @forelse($products as $product)
                            @php
                                $isLow = $product->stock_quantity <= ($product->alert_quantity ?? 5);
                            @endphp
                            <div class="col-6 col-md-4 col-xl-3 mb-2">
                                <div class="card h-100 shadow-sm product-card border-0 bg-light" 
                                     data-id="{{ $product->id }}" 
                                     data-stock="{{ $product->stock_quantity }}"
                                     style="cursor: pointer; transition: transform 0.15s ease-in-out;">
                                    <div class="position-relative">
                                        <img src="{{ $product->image }}" class="card-img-top bg-white p-2" 
                                             alt="{{ $product->name }}" 
                                             style="height: 110px; object-fit: contain;">
                                        @if($product->barcode)
                                            <span class="badge bg-secondary position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">
                                                {{ $product->barcode }}
                                            </span>
                                        @endif
                                        @if($isLow && $product->stock_quantity > 0)
                                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-1" style="font-size: 0.7rem;">
                                                Low: {{ $product->stock_quantity }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="card-body p-2 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title text-truncate mb-0" title="{{ $product->name }}" style="font-size: 0.85rem;">
                                                {{ $product->name }}
                                            </h6>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $product->category ?? '-' }}</small>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="fw-bold text-primary" style="font-size: 0.95rem;">
                                                ${{ number_format($product->price, 2) }}
                                            </span>

                                            @if ($product->stock_quantity > 0)
                                                <button type="button" class="btn btn-sm btn-primary py-0 px-2 quick-add-btn" 
                                                        data-id="{{ $product->id }}">
                                                    + Add
                                                </button>
                                            @else
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">Out</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <h5>No products found in this category.</h5>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-center mt-2">
                        {{ $products->appends(request()->query())->links('layouts._pagination') }}
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Real-Time Live Cart & Checkout Terminal (35%) --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 15px;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold">🛒 Current Cart</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2" id="holdCartBtn" title="Hold / Park this Order (F8)">
                            ⏸ Hold
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="clearCartBtn" title="Clear Cart">
                            🗑 Clear
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">

                    {{-- Customer Selector + Quick Add --}}
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Customer</label>
                        <div class="input-group input-group-sm">
                            <select id="terminal_customer_id" class="form-select">
                                <option value="" disabled selected>Select Customer</option>
                                @foreach (App\Models\Customer::orderBy('name')->get() as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }} ({{ $customer->phone ?? 'No phone' }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                + New
                            </button>
                        </div>
                    </div>

                    {{-- Cart Items List Container --}}
                    <div class="table-responsive border rounded mb-3" style="max-height: 230px; min-height: 150px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="terminalCartTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Item</th>
                                    <th style="width: 105px; text-align: center;">Qty</th>
                                    <th style="text-align: right;">Total</th>
                                    <th style="width: 25px;"></th>
                                </tr>
                            </thead>
                            <tbody id="terminalCartBody">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Scan barcode or click an item to add.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Financial Calculations Summary --}}
                    <div class="bg-light p-2 rounded mb-2 border">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Subtotal:</span>
                            <span id="terminalSubtotal" class="fw-bold text-dark">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Discount ($):</span>
                            <input type="number" step="0.01" min="0" id="terminalDiscount" 
                                   class="form-control form-control-sm text-end" style="width: 90px;" value="0.00">
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6">Payable Total:</span>
                            <span id="terminalFinalTotal" class="fw-bold fs-4 text-primary">$0.00</span>
                        </div>
                    </div>

                    {{-- Payment Method Selector --}}
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Payment Method</label>
                        <div class="btn-group w-100 btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="terminal_pay_method" id="term_cash" value="cash" checked>
                            <label class="btn btn-outline-success" for="term_cash">💵 Cash</label>

                            <input type="radio" class="btn-check" name="terminal_pay_method" id="term_card" value="card">
                            <label class="btn btn-outline-primary" for="term_card">💳 Card</label>

                            <input type="radio" class="btn-check" name="terminal_pay_method" id="term_transfer" value="transfer">
                            <label class="btn btn-outline-info" for="term_transfer">🏦 Transfer</label>
                        </div>
                    </div>

                    {{-- Cash Received & Change Calculations (Only visible when Cash) --}}
                    <div id="terminalCashBox" class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Cash Tendered ($)</label>
                            <input type="number" step="0.01" min="0" id="terminalTendered" class="form-control form-control-sm text-end fw-bold" value="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Change Due ($)</label>
                            <input type="text" id="terminalChange" class="form-control form-control-sm text-end fw-bold text-success bg-white" value="$0.00" readonly>
                        </div>
                    </div>

                    {{-- Optional Order Note --}}
                    <input type="text" id="terminalNotes" class="form-control form-control-sm mb-3" placeholder="Order note (optional, e.g. Table 2)">

                    {{-- Checkout Button --}}
                    <button type="button" class="btn btn-success btn-lg w-100 fw-bold py-2 shadow" id="terminalCheckoutBtn">
                        ✔ Pay & Complete (F4)
                    </button>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL 1: Order Completed Success Receipt Modal --}}
<div class="modal fade" id="orderSuccessModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div class="fs-1 text-success mb-2">✅</div>
                <h4 class="fw-bold mb-1" id="successOrderTitle">Order Placed Successfully!</h4>
                <p class="text-muted small" id="successOrderSubtitle"></p>
                
                <div class="d-flex justify-content-center gap-2 my-3">
                    <a href="#" id="successThermalBtn" target="_blank" class="btn btn-outline-dark">
                        🧾 Print 80mm Thermal Receipt
                    </a>
                    <a href="#" id="successInvoiceBtn" target="_blank" class="btn btn-outline-primary">
                        🖨 Print A4 Invoice
                    </a>
                </div>

                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" id="nextCustomerBtn">
                    🆕 Next Customer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Held / Parked Orders Modal --}}
<div class="modal fade" id="heldOrdersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">⏸ Parked / Held Orders</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref / Note</th>
                            <th>Customer</th>
                            <th>Items Count</th>
                            <th>Subtotal</th>
                            <th>Held At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="heldOrdersTableBody">
                        <tr><td colspan="6" class="text-center py-4 text-muted">No held orders right now.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 3: Open Shift Modal --}}
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('shifts.open') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">⏱️ Open New Shift</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opening Cash Float ($):</label>
                        <input type="number" step="0.01" min="0" name="opening_cash" class="form-control" value="100.00" required autofocus>
                        <small class="text-muted">Enter the initial cash balance in the cash drawer.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional):</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Morning Shift">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Shift</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: Close Shift Modal --}}
@if($activeShift)
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('shifts.close', $activeShift->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">🔒 Close Shift #{{ $activeShift->id }} & Generate Z-Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <strong>Expected Cash:</strong> ${{ number_format($activeShift->opening_cash + $activeShift->cash_sales, 2) }}
                        (Float: ${{ number_format($activeShift->opening_cash, 2) }} + Cash Sales: ${{ number_format($activeShift->cash_sales, 2) }})
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actual Counted Cash in Drawer ($):</label>
                        <input type="number" step="0.01" min="0" name="closing_cash" class="form-control fs-5" required autofocus>
                        <small class="text-muted">Count all cash currently in the drawer and enter the total.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Closing Notes (Optional):</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Minor cash discrepancy explanation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Close Shift & Print Z-Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL 5: Add Customer Fast Modal --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addCustomerForm">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">+ Quick Add Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addCustomerErrors" class="text-danger small mb-2"></div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-success">Save Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const CSRF_TOKEN = "{{ csrf_token() }}";

    document.addEventListener('DOMContentLoaded', function () {
        // Elements
        const barcodeInput = document.getElementById('barcodeInput');
        const barcodeBtn = document.getElementById('barcodeBtn');
        const barcodeFeedback = document.getElementById('barcodeFeedback');
        const searchInput = document.getElementById('searchInput');
        const grid = document.getElementById('productsGrid');

        const terminalCartBody = document.getElementById('terminalCartBody');
        const terminalSubtotal = document.getElementById('terminalSubtotal');
        const terminalDiscount = document.getElementById('terminalDiscount');
        const terminalFinalTotal = document.getElementById('terminalFinalTotal');
        const terminalTendered = document.getElementById('terminalTendered');
        const terminalChange = document.getElementById('terminalChange');
        const terminalCashBox = document.getElementById('terminalCashBox');
        const terminalCheckoutBtn = document.getElementById('terminalCheckoutBtn');
        const terminalCustomer = document.getElementById('terminal_customer_id');
        const terminalNotes = document.getElementById('terminalNotes');
        const clearCartBtn = document.getElementById('clearCartBtn');
        const holdCartBtn = document.getElementById('holdCartBtn');
        const heldOrdersBtn = document.getElementById('heldOrdersBtn');
        const heldOrdersCount = document.getElementById('heldOrdersCount');
        const heldOrdersTableBody = document.getElementById('heldOrdersTableBody');

        let cartSubtotal = 0;

        // Fetch & Refresh Live Cart Panel
        function refreshCart() {
            fetch("{{ route('cart.api.data') }}")
                .then(res => res.json())
                .then(data => {
                    cartSubtotal = data.subtotal;
                    renderCartItems(data.items);
                    recalculateTotals();
                    refreshHeldCount();
                })
                .catch(err => console.error("Error refreshing cart:", err));
        }

        function renderCartItems(items) {
            if (!items || items.length === 0) {
                terminalCartBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Scan barcode or click an item to add.</td>
                    </tr>
                `;
                return;
            }

            let rows = '';
            items.forEach(item => {
                rows += `
                    <tr>
                        <td class="p-1">
                            <strong class="d-block text-truncate" style="max-width: 130px; font-size: 0.82rem;">${item.name}</strong>
                            <small class="text-muted">$${parseFloat(item.price).toFixed(2)}</small>
                        </td>
                        <td class="p-1 text-center">
                            <div class="input-group input-group-sm justify-content-center" style="width: 95px;">
                                <button class="btn btn-outline-secondary px-2 py-0 update-qty-btn" data-id="${item.id}" data-action="decrease">-</button>
                                <input type="text" class="form-control text-center p-0" value="${item.quantity}" readonly style="max-width: 32px;">
                                <button class="btn btn-outline-secondary px-2 py-0 update-qty-btn" data-id="${item.id}" data-action="increase" data-max="${item.stock}">+</button>
                            </div>
                        </td>
                        <td class="p-1 text-end fw-bold" style="font-size: 0.85rem;">
                            $${parseFloat(item.subtotal).toFixed(2)}
                        </td>
                        <td class="p-1 text-center">
                            <button class="btn btn-link text-danger p-0 remove-item-btn" data-id="${item.id}" style="text-decoration: none;">✕</button>
                        </td>
                    </tr>
                `;
            });

            terminalCartBody.innerHTML = rows;
            attachCartEvents();
        }

        function attachCartEvents() {
            // Qty update
            document.querySelectorAll('.update-qty-btn').forEach(btn => {
                btn.onclick = function() {
                    const id = this.dataset.id;
                    const action = this.dataset.action;
                    const input = this.parentElement.querySelector('input');
                    let currentQty = parseInt(input.value);

                    if (action === 'increase') {
                        const max = parseInt(this.dataset.max);
                        if (currentQty >= max) {
                            alert(`Maximum available stock is ${max}`);
                            return;
                        }
                        currentQty++;
                    } else if (action === 'decrease') {
                        if (currentQty <= 1) return;
                        currentQty--;
                    }

                    fetch(`/cart/item/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({ quantity: currentQty })
                    }).then(() => refreshCart());
                };
            });

            // Remove item
            document.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.onclick = function() {
                    const id = this.dataset.id;
                    fetch(`/cart/item/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        }
                    }).then(() => refreshCart());
                };
            });
        }

        function recalculateTotals() {
            const discount = Math.max(0, parseFloat(terminalDiscount.value) || 0);
            const finalTotal = Math.max(0, cartSubtotal - discount);

            terminalSubtotal.textContent = `$${cartSubtotal.toFixed(2)}`;
            terminalFinalTotal.textContent = `$${finalTotal.toFixed(2)}`;

            const selectedMethod = document.querySelector('input[name="terminal_pay_method"]:checked')?.value || 'cash';
            if (selectedMethod === 'cash') {
                terminalCashBox.style.display = '';
                if (!terminalTendered.dataset.dirty) {
                    terminalTendered.value = finalTotal.toFixed(2);
                }
                const tendered = parseFloat(terminalTendered.value) || 0;
                const change = Math.max(0, tendered - finalTotal);
                terminalChange.value = `$${change.toFixed(2)}`;
            } else {
                terminalCashBox.style.display = 'none';
            }
        }

        terminalDiscount.oninput = recalculateTotals;
        terminalTendered.oninput = function() {
            this.dataset.dirty = "true";
            recalculateTotals();
        };

        document.querySelectorAll('input[name="terminal_pay_method"]').forEach(r => {
            r.onchange = function() {
                delete terminalTendered.dataset.dirty;
                recalculateTotals();
            };
        });

        // Add Product via Quick Add button
        document.querySelectorAll('.quick-add-btn').forEach(btn => {
            btn.onclick = function(e) {
                e.stopPropagation();
                const prodId = this.dataset.id;
                fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ product_id: prodId, quantity: 1 })
                }).then(() => refreshCart());
            };
        });

        // Barcode Scan
        function doBarcodeScan() {
            const code = barcodeInput.value.trim();
            if (!code) return;

            barcodeFeedback.className = 'small text-muted';
            barcodeFeedback.textContent = 'Scanning...';

            fetch("{{ route('cart.add.barcode') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ barcode: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    barcodeFeedback.className = 'small text-success fw-bold';
                    barcodeFeedback.textContent = `✔ Added: ${data.product.name}`;
                    barcodeInput.value = '';
                    refreshCart();
                } else {
                    barcodeFeedback.className = 'small text-danger fw-bold';
                    barcodeFeedback.textContent = `✖ ${data.message || 'Product not found'}`;
                }
            })
            .catch(() => {
                barcodeFeedback.className = 'small text-danger fw-bold';
                barcodeFeedback.textContent = '✖ Error processing scan';
            });
        }

        barcodeBtn.onclick = doBarcodeScan;
        barcodeInput.onkeypress = function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doBarcodeScan();
            }
        };

        // Live Search
        let searchTimeout;
        searchInput.oninput = function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = searchInput.value.trim();
                fetch(`/products/search?search=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(products => {
                        let html = '';
                        if (products.length > 0) {
                            products.forEach(p => {
                                const isLow = p.stock_quantity <= (p.alert_quantity || 5);
                                const lowBadge = isLow && p.stock_quantity > 0 ? 
                                    `<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-1" style="font-size: 0.7rem;">Low: ${p.stock_quantity}</span>` : '';
                                const barcodeBadge = p.barcode ? 
                                    `<span class="badge bg-secondary position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">${p.barcode}</span>` : '';
                                const actionBtn = p.stock_quantity > 0 ? 
                                    `<button type="button" class="btn btn-sm btn-primary py-0 px-2 quick-add-btn" data-id="${p.id}">+ Add</button>` :
                                    `<span class="badge bg-danger" style="font-size: 0.7rem;">Out</span>`;

                                html += `
                                    <div class="col-6 col-md-4 col-xl-3 mb-2">
                                        <div class="card h-100 shadow-sm border-0 bg-light">
                                            <div class="position-relative">
                                                <img src="${p.image || '/uploads/default.jpg'}" class="card-img-top bg-white p-2" style="height: 110px; object-fit: contain;">
                                                ${barcodeBadge}
                                                ${lowBadge}
                                            </div>
                                            <div class="card-body p-2 d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="card-title text-truncate mb-0" style="font-size: 0.85rem;">${p.name}</h6>
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">${p.category || '-'}</small>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="fw-bold text-primary" style="font-size: 0.95rem;">$${parseFloat(p.price).toFixed(2)}</span>
                                                    ${actionBtn}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = `<div class="col-12 text-center py-4 text-muted">No products match your search.</div>`;
                        }
                        grid.innerHTML = html;

                        // Re-bind click
                        document.querySelectorAll('.quick-add-btn').forEach(b => {
                            b.onclick = function() {
                                fetch("{{ route('cart.add') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN
                                    },
                                    body: JSON.stringify({ product_id: this.dataset.id, quantity: 1 })
                                }).then(() => refreshCart());
                            };
                        });
                    });
            }, 250);
        };

        // Checkout Button Click (AJAX)
        terminalCheckoutBtn.onclick = function() {
            const customerId = terminalCustomer.value;
            if (!customerId) {
                alert("Please select a customer before checkout.");
                terminalCustomer.focus();
                return;
            }

            if (cartSubtotal <= 0) {
                alert("Cart is empty. Add products before completing order.");
                return;
            }

            const method = document.querySelector('input[name="terminal_pay_method"]:checked')?.value || 'cash';
            const discount = parseFloat(terminalDiscount.value) || 0;
            const tendered = parseFloat(terminalTendered.value) || 0;
            const notes = terminalNotes.value;

            terminalCheckoutBtn.disabled = true;
            terminalCheckoutBtn.textContent = 'Processing...';

            fetch("{{ route('checkout.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    payment_method: method,
                    discount: discount,
                    paid_amount: method === 'cash' ? tendered : null,
                    notes: notes
                })
            })
            .then(res => res.json())
            .then(data => {
                terminalCheckoutBtn.disabled = false;
                terminalCheckoutBtn.textContent = '✔ Pay & Complete (F4)';

                if (data.status === 'success') {
                    // Show success modal with Thermal & Invoice links
                    document.getElementById('successOrderTitle').textContent = `Order #${data.order_id} Completed!`;
                    document.getElementById('successOrderSubtitle').textContent = `Change Due: ${terminalChange.value}`;
                    document.getElementById('successThermalBtn').href = data.thermal_url;
                    document.getElementById('successInvoiceBtn').href = data.invoice_url;

                    const successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
                    successModal.show();

                    // Reset form fields
                    terminalDiscount.value = "0.00";
                    terminalNotes.value = "";
                    delete terminalTendered.dataset.dirty;

                    refreshCart();
                } else {
                    alert(data.message || 'Checkout failed.');
                }
            })
            .catch(err => {
                terminalCheckoutBtn.disabled = false;
                terminalCheckoutBtn.textContent = '✔ Pay & Complete (F4)';
                alert("Checkout error. Please verify drawer/stock.");
                console.error(err);
            });
        };

        // Next Customer
        document.getElementById('nextCustomerBtn').onclick = function() {
            barcodeInput.focus();
        };

        // Clear Cart
        clearCartBtn.onclick = function() {
            if (confirm("Are you sure you want to clear the entire cart?")) {
                fetch("{{ route('cart.clear') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                }).then(() => refreshCart());
            }
        };

        // Hold / Park Order
        holdCartBtn.onclick = function() {
            if (cartSubtotal <= 0) {
                alert("Cannot hold an empty cart.");
                return;
            }

            const ref = prompt("Enter reference note for this parked order (e.g. Table 4, Red jacket):", "Hold #" + (new Date()).toLocaleTimeString());
            if (ref === null) return;

            fetch("{{ route('held.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    reference_note: ref,
                    customer_id: terminalCustomer.value || null,
                    discount: parseFloat(terminalDiscount.value) || 0
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("Order parked successfully!");
                    refreshCart();
                } else {
                    alert(data.message || 'Failed to hold order.');
                }
            });
        };

        // Refresh Held Count & Table
        function refreshHeldCount() {
            fetch("{{ route('held.index') }}")
                .then(res => res.json())
                .then(helds => {
                    heldOrdersCount.textContent = helds.length;
                    if (helds.length === 0) {
                        heldOrdersTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No held orders right now.</td></tr>`;
                        return;
                    }

                    let rows = '';
                    helds.forEach(h => {
                        rows += `
                            <tr>
                                <td><strong>${h.reference_note || 'Held Order'}</strong></td>
                                <td>${h.customer ? h.customer.name : 'Walk-in'}</td>
                                <td><span class="badge bg-secondary">${h.cart_data ? h.cart_data.length : 0} items</span></td>
                                <td><strong>$${parseFloat(h.total).toFixed(2)}</strong></td>
                                <td><small class="text-muted">${new Date(h.created_at).toLocaleTimeString()}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-success recall-held-btn" data-id="${h.id}">Recall to Cart</button>
                                    <button class="btn btn-sm btn-outline-danger discard-held-btn" data-id="${h.id}">✕</button>
                                </td>
                            </tr>
                        `;
                    });
                    heldOrdersTableBody.innerHTML = rows;

                    // Bind recall
                    document.querySelectorAll('.recall-held-btn').forEach(b => {
                        b.onclick = function() {
                            const id = this.dataset.id;
                            fetch(`/held-orders/${id}/recall`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    if (data.customer_id) terminalCustomer.value = data.customer_id;
                                    if (data.discount) terminalDiscount.value = data.discount;
                                    bootstrap.Modal.getInstance(document.getElementById('heldOrdersModal')).hide();
                                    refreshCart();
                                }
                            });
                        };
                    });

                    // Bind discard
                    document.querySelectorAll('.discard-held-btn').forEach(b => {
                        b.onclick = function() {
                            if (!confirm("Discard this held order?")) return;
                            const id = this.dataset.id;
                            fetch(`/held-orders/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                            }).then(() => refreshHeldCount());
                        };
                    });
                });
        }

        // Quick Add Customer AJAX Form
        const addCustForm = document.getElementById('addCustomerForm');
        addCustForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(addCustForm);

            fetch("{{ route('customers.store.ajax') }}", {
                method: "POST",
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const opt = document.createElement('option');
                    opt.value = data.customer.id;
                    opt.textContent = `${data.customer.name} (${data.customer.phone || 'No phone'})`;
                    opt.selected = true;
                    terminalCustomer.appendChild(opt);

                    bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                    addCustForm.reset();
                }
            });
        };

        // Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            } else if (e.key === 'F3') {
                e.preventDefault();
                barcodeInput.focus();
                barcodeInput.select();
            } else if (e.key === 'F4') {
                e.preventDefault();
                terminalCheckoutBtn.click();
            } else if (e.key === 'F8') {
                e.preventDefault();
                holdCartBtn.click();
            }
        });

        // Initialize Cart on Load
        refreshCart();
    });
</script>
@endsection
