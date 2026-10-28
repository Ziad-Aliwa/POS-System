@extends('layouts.layout')

@section('title', 'Products')

@section('content')
    <h2 class="mb-4">Products</h2>

    {{-- Search Form with Live Typing & Server Submit --}}
    <form method="GET" action="{{ route('products.all_products') }}" class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-secondary text-white">🔍</span>
            <input type="text" name="search" id="searchInput" class="form-control" 
                   placeholder="Search products by name, category, or barcode..." 
                   value="{{ $search ?? request('search') }}" autocomplete="off">
            <button class="btn btn-primary" type="submit">Search</button>
            @if(request('search'))
                <a href="{{ route('products.all_products') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    {{-- Add Product (Admin only) --}}
    @if (auth()->user()->role === 'admin')
        <div class="mb-3">
            <a href="{{ route('products.create') }}" class="btn btn-success">+ Add Product</a>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="productsTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Barcode</th>
                    <th>Category</th>
                    <th>Selling Price</th>
                    <th>Cost Price</th>
                    <th>Margin</th>
                    <th>Stock Balance</th>
                    @if (auth()->user()->role === 'admin')
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $margin = $product->price - ($product->cost_price ?? 0);
                        $isLow = $product->stock_quantity <= ($product->alert_quantity ?? 5);
                    @endphp
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td class="product-name fw-bold">
                            {{ $product->name }}
                            @if($isLow)
                                <span class="badge bg-danger ms-1">Low Stock</span>
                            @endif
                        </td>
                        <td class="product-barcode"><span class="badge bg-secondary">{{ $product->barcode ?? '-' }}</span></td>
                        <td class="product-category">{{ $product->category ?? '-' }}</td>
                        <td><strong>${{ number_format($product->price, 2) }}</strong></td>
                        <td class="text-muted">${{ number_format($product->cost_price ?? 0, 2) }}</td>
                        <td class="text-success fw-bold">+${{ number_format($margin, 2) }}</td>
                        <td>
                            @if($product->stock_quantity > 0)
                                <span class="badge {{ $isLow ? 'bg-warning text-dark' : 'bg-success' }} fs-6">
                                    {{ $product->stock_quantity }}
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">0 (Out)</span>
                            @endif
                        </td>
                                <span class="badge bg-danger">0</span>
                            @endif
                        </td>

                        @if (auth()->user()->role === 'admin')
                            <td class="d-flex gap-1">
                                <a href="{{ route('products.show', $product->id) }}"
                                    class="btn btn-sm btn-info text-white">View</a>

                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        @endif

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->appends(request()->query())->links() }}
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('searchInput');
            if (!input) return;

            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                document.querySelectorAll('#productsTable tbody tr').forEach(row => {
                    const nameEl = row.querySelector('.product-name');
                    if (!nameEl) return;
                    const name = nameEl.textContent.toLowerCase();
                    const category = row.querySelector('.product-category')?.textContent.toLowerCase() || '';
                    const barcode = row.querySelector('.product-barcode')?.textContent.toLowerCase() || '';

                    const match = name.includes(searchTerm) || category.includes(searchTerm) || barcode.includes(searchTerm);
                    row.style.display = match ? '' : 'none';
                });
            });
        });
    </script>
@endsection
