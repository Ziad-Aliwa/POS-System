@extends('layouts.layout')

@section('title', 'Products')

@section('content')
    <h2 class="mb-4">Products</h2>

    {{-- Live Search --}}
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name or category...">
    </div>

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
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    @if (auth()->user()->role === 'admin')
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td class="product-name">{{ $product->name }}</td>
                        <td class="product-category">{{ $product->category ?? '-' }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->stock_quantity }}</td>

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
                        <td colspan="6" class="text-center">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('searchInput');
            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('#productsTable tbody tr').forEach(row => {
                    const name = row.querySelector('.product-name').textContent.toLowerCase();
                    const category = row.querySelector('.product-category').textContent
                    .toLowerCase();
                    const match = name.includes(searchTerm) || category.includes(searchTerm);
                    row.style.display = match ? '' : 'none';
                });
            });
        });
    </script>
@endsection
