@extends('layouts.layout')

@section('title', 'Product Details')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Details</h5>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">← Back</a>
        </div>

        <div class="card-body row">
            {{-- Product Image --}}
            <div class="col-md-4">
                @if ($product->image)
                    <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}"
                        style="height: 150px; object-fit: contain; background-color: #ffffff;">
                @else
                    <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image"
                        style="height: 150px; object-fit: contain; background-color: #ffffff;">
                @endif
            </div>

            {{-- Product Info --}}
            <div class="col-md-8">
                <h4>{{ $product->name }}</h4>

                <p><strong>Category:</strong> {{ $product->category ?? '-' }}</p>
                <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                <p><strong>Stock Quantity:</strong>
                    @if ($product->stock_quantity > 0)
                        <span class="text-success">{{ $product->stock_quantity }}</span>
                    @else
                        <span class="text-danger">Out of stock</span>
                    @endif
                </p>

                <div class="mt-3">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
