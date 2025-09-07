@extends('layouts.layout')

@section('title', 'Products')

@section('content')
    <h2 class="mb-4">Products</h2>

    {{-- Live Search --}}
    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Live search by name or category">

    {{-- Products Grid --}}
    <div class="row" id="productsGrid">
        @forelse($products as $product)
            <div class="col-sm-6 col-md-3 mb-4">
                <div class="card h-100 shadow-sm" style="font-size: 0.9rem;">
                    {{-- @if ($product->image) --}}
                        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}"
                            style="height: 150px; object-fit: contain; background-color: #ffffff;">
                    {{-- @else
                        <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image"
                            style="height: 150px; object-fit: contain; background-color: #ffffff;">
                    @endif --}}
                    <div class="card-body d-flex flex-column p-2">
                        <h6 class="card-title mb-1">{{ $product->name }}</h6>
                        <small class="text-muted">{{ $product->category ?? '-' }}</small>
                        <p class="fw-bold text-primary mb-1">${{ number_format($product->price, 2) }}</p>
                        <p class="mb-2">
                            @if ($product->stock_quantity > 0)
                                <span class="text-success">In Stock ({{ $product->stock_quantity }})</span>
                            @else
                                <span class="text-danger">Out of Stock</span>
                            @endif
                        </p>

                        @if ($product->stock_quantity > 0)
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-auto d-flex">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="number" name="quantity" value="1" min="1"
                                    max="{{ $product->stock_quantity }}" class="form-control form-control-sm me-2"
                                    style="width: 60px;">

                                <button type="submit" class="btn btn-sm btn-primary">Add to cart</button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-secondary mt-auto" disabled>Add</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <p>No products found.</p>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $products->appends(request()->query())->links('layouts._pagination') }}
    </div>
@endsection

{{-- CSRF token --}}
<script>
    const csrfToken = "{{ csrf_token() }}";
</script>

{{-- Live Search Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const grid = document.getElementById('productsGrid');
        let timeout;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                let query = input.value;

                fetch(`/products/search?search=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(products => {
                        let rows = '';

                        if (products.length > 0) {
                            products.forEach(product => {
                                let stockDisplay = product.stock_quantity > 0 ?
                                    `<span class="text-success">In Stock (${product.stock_quantity})</span>` :
                                    `<span class="text-danger">Out of Stock</span>`;

                                let form = product.stock_quantity > 0 ?
                                    `
                                    <form action="/cart/add" method="POST" class="mt-auto d-flex">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="product_id" value="${product.id}">
                                        <input type="number" name="quantity" value="1" min="1"
                                               max="${product.stock_quantity}"
                                               class="form-control form-control-sm me-2" style="width: 60px;">
                                        <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                    </form>` :
                                    `<button class="btn btn-sm btn-secondary mt-auto" disabled>Add</button>`;

                                let image = product.image ?
                                    `/storage/${product.image}` :
                                    'https://via.placeholder.com/300x200?text=No+Image';

                                rows += `
                                <div class="col-sm-6 col-md-3 mb-4">
                                    <div class="card h-100 shadow-sm" style="font-size: 0.9rem;">
                                        <img src="${image}" class="card-img-top" alt="${product.name}" style="height: 150px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column p-2">
                                            <h6 class="card-title mb-1">${product.name}</h6>
                                            <small class="text-muted">${product.category ?? '-'}</small>
                                            <p class="fw-bold text-primary mb-1">$${parseFloat(product.price).toFixed(2)}</p>
                                            <p class="mb-2">${stockDisplay}</p>
                                            ${form}
                                        </div>
                                    </div>
                                </div>`;
                            });
                        } else {
                            rows =
                                `<div class="col-12 text-center"><p>No products found.</p></div>`;
                        }

                        grid.innerHTML = rows;
                    });
            }, 300);
        });
    });
</script>
