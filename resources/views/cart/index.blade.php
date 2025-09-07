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

        {{-- Checkout Form --}}
        @if ($items->count())
            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="customer_id" class="form-label fw-semibold">Choose Customer:</label>

                    <select name="customer_id" id="customer_id"
                        class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="" disabled selected>Select a customer</option>
                        @foreach (App\Models\Customer::orderBy('name')->get() as $customer)
                            <option value="{{ $customer->id }}"
                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="total" value="{{ $total }}">
                <button type="submit" class="btn btn-primary">Checkout</button>
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="modal"
                    data-bs-target="#addCustomerModal">
                    + Add Customer
                </button>
            </form>
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

        function updateCartTotal() {
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(input => {
                const itemId = input.dataset.id;
                const quantity = parseInt(input.value);
                const price = parseFloat(document.getElementById(`subtotal-${itemId}`).dataset.price);
                total += quantity * price;
            });

            document.getElementById('cart-total').textContent = `$${total.toFixed(2)}`;
        }
    });
</script>
