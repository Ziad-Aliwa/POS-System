@extends('layouts.layout')

@section('title', 'My Cart')

@section('content')
    <div class="container">
        <h2 class="mb-4">Shopping Cart</h2>

  

        {{-- Cart Items Table --}}
        @if ($items->count())
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach ($items as $item)
                        @php
                            $subtotal = $item->product->price * $item->quantity;
                            $total += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>${{ number_format($item->product->price, 2) }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 update-qty-btn"
                                        data-action="decrease" data-id="{{ $item->id }}">-</button>

                                    <input type="number" value="{{ $item->quantity }}" min="1"
                                        class="form-control form-control-sm text-center qty-input"
                                        data-id="{{ $item->id }}" data-max="{{ $item->product->stock_quantity }}"
                                        style="width: 60px;" readonly>


                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1 update-qty-btn"
                                        data-action="increase" data-id="{{ $item->id }}">+</button>
                                </div>
                            </td>


                            <td id="subtotal-{{ $item->id }}" data-price="{{ $item->product->price }}">
                                ${{ number_format($item->product->price * $item->quantity, 2) }}
                            </td>
                            <td>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="table-secondary">
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td colspan="2" class="fw-bold" id="cart-total">${{ number_format($total, 2) }}</td>
                    </tr>

                </tbody>
            </table>
        @else
            <p class="text-muted">Your cart is currently empty.</p>
        @endif

        {{-- Checkout Form & Payment Panel --}}
        @if ($items->count())
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    💳 Complete Transaction & Payment
                </div>
                <div class="card-body">
                    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf
                        <div class="row g-3">
                            {{-- Customer Selection --}}
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label fw-semibold">Choose Customer:</label>
                                <div class="input-group">
                                    <select name="customer_id" id="customer_id"
                                        class="form-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select a customer</option>
                                        @foreach (App\Models\Customer::orderBy('name')->get() as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->phone ?? 'No phone' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#addCustomerModal">
                                        + New Customer
                                    </button>
                                </div>
                                @error('customer_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Method Selection --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked autocomplete="off">
                                    <label class="btn btn-outline-success" for="pay_cash">💵 Cash</label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="pay_card">💳 Card</label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_transfer" value="transfer" autocomplete="off">
                                    <label class="btn btn-outline-info" for="pay_transfer">🏦 Transfer</label>
                                </div>
                            </div>

                            {{-- Discount Input --}}
                            <div class="col-md-4">
                                <label for="discount" class="form-label fw-semibold">Discount ($):</label>
                                <input type="number" step="0.01" min="0" name="discount" id="discountInput" 
                                       class="form-control" value="0.00">
                            </div>

                            {{-- Cash Received (Only for Cash) --}}
                            <div class="col-md-4" id="cashReceivedContainer">
                                <label for="paidAmountInput" class="form-label fw-semibold">Cash Tendered / Received ($):</label>
                                <input type="number" step="0.01" min="0" name="paid_amount" id="paidAmountInput" 
                                       class="form-control" value="{{ number_format($total, 2, '.', '') }}">
                            </div>

                            {{-- Change / الباقي --}}
                            <div class="col-md-4" id="changeAmountContainer">
                                <label class="form-label fw-semibold text-success">Change / Remaining ($):</label>
                                <input type="text" id="changeAmountDisplay" class="form-control fw-bold text-success bg-light" 
                                       value="$0.00" readonly>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">Order Notes (Optional):</label>
                                <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Table 5, expedited delivery, etc.">
                            </div>
                        </div>

                        {{-- Calculation Summary --}}
                        <div class="border rounded p-3 mt-4 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">Subtotal: <strong id="summary-subtotal">${{ number_format($total, 2) }}</strong></span> | 
                                <span class="text-muted">Discount: <strong id="summary-discount" class="text-danger">-$0.00</strong></span>
                            </div>
                            <div>
                                <span class="fs-4 fw-bold me-3">Total Payable: <span id="summary-final-total" class="text-primary">${{ number_format($total, 2) }}</span></span>
                                <button type="submit" class="btn btn-success btn-lg px-4">
                                    ✔ Process Checkout
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif


        <!-- Add Customer Modal -->
        <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form id="addCustomerForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="addCustomerErrors" class="text-danger small mb-2"></div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control" required>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Save Customer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addCustomerForm');
        const errorDiv = document.getElementById('addCustomerErrors');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            fetch("{{ route('customers.store.ajax') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Close modal and update select list
                        const select = document.getElementById('customer_id');
                        const option = document.createElement('option');
                        option.value = data.customer.id;
                        option.textContent = data.customer.name;
                        option.selected = true;
                        select.appendChild(option);

                        document.getElementById('addCustomerModal').querySelector('.btn-close')
                            .click();
                        form.reset();
                        errorDiv.innerHTML = '';
                    } else {
                        errorDiv.innerHTML = data.message || 'An error occurred.';
                    }
                })
                .catch(err => {
                    errorDiv.innerHTML = 'Something went wrong.';
                    console.error(err);
                });
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = '{{ csrf_token() }}';

        document.querySelectorAll('.update-qty-btn').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;
                const action = this.dataset.action;
                const input = document.querySelector(`.qty-input[data-id="${itemId}"]`);
                let quantity = parseInt(input.value);

                if (action === 'increase') {
                    const maxStock = parseInt(input.dataset.max);
                    if (quantity < maxStock) {
                        quantity += 1;
                    } else {
                        alert(`Maximum available stock is ${maxStock}`);
                        return;
                    }
                } else if (action === 'decrease' && quantity > 1) {
                    quantity -= 1;
                } else {
                    return;
                }


                fetch(`/cart/item/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            quantity: quantity
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            input.value = quantity;

                            const price = parseFloat(data.price);
                            const subtotal = (price * quantity).toFixed(2);
                            const subtotalCell = document.getElementById(
                                `subtotal-${itemId}`);
                            subtotalCell.textContent = `$${subtotal}`;

                            updateCartTotal();
                        } else {
                            alert(data.message || 'Update failed.');
                        }
                    })
                    .catch(err => {
                        alert('Error updating quantity.');
                        console.error(err);
                    });
            });
        });

        const discountInput = document.getElementById('discountInput');
        const paidAmountInput = document.getElementById('paidAmountInput');
        const changeAmountDisplay = document.getElementById('changeAmountDisplay');
        const cashContainer = document.getElementById('cashReceivedContainer');
        const changeContainer = document.getElementById('changeAmountContainer');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryDiscount = document.getElementById('summary-discount');
        const summaryFinalTotal = document.getElementById('summary-final-total');

        function recalculatePayment() {
            let subtotal = 0;
            document.querySelectorAll('.qty-input').forEach(input => {
                const itemId = input.dataset.id;
                const quantity = parseInt(input.value);
                const price = parseFloat(document.getElementById(`subtotal-${itemId}`).dataset.price);
                subtotal += quantity * price;
            });

            const discount = discountInput ? Math.max(0, parseFloat(discountInput.value) || 0) : 0;
            const finalTotal = Math.max(0, subtotal - discount);

            if (summarySubtotal) summarySubtotal.textContent = `$${subtotal.toFixed(2)}`;
            if (summaryDiscount) summaryDiscount.textContent = `-$${discount.toFixed(2)}`;
            if (summaryFinalTotal) summaryFinalTotal.textContent = `$${finalTotal.toFixed(2)}`;
            document.getElementById('cart-total').textContent = `$${subtotal.toFixed(2)}`;

            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
            if (selectedPaymentMethod === 'cash') {
                if (cashContainer) cashContainer.style.display = '';
                if (changeContainer) changeContainer.style.display = '';
                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                const change = Math.max(0, paidAmount - finalTotal);
                if (changeAmountDisplay) changeAmountDisplay.value = `$${change.toFixed(2)}`;
            } else {
                if (cashContainer) cashContainer.style.display = 'none';
                if (changeContainer) changeContainer.style.display = 'none';
            }
        }

        if (discountInput) {
            discountInput.addEventListener('input', recalculatePayment);
        }
        if (paidAmountInput) {
            paidAmountInput.addEventListener('input', recalculatePayment);
        }
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', recalculatePayment);
        });

        function updateCartTotal() {
            recalculatePayment();
        }

        recalculatePayment();
    });
</script>
