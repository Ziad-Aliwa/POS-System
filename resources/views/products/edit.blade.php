@extends('layouts.layout')

@section('title', 'Edit Product')

@section('content')
    <h2 class="mb-4">Edit Product</h2>

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $product->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                value="{{ old('category', $product->category) }}" required>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Price ($)</label>
            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                value="{{ old('price', $product->price) }}" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
            @error('stock_quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Image (optional)</label>
            <input type="file" name="image" class="form-control">
            <br>
            @if ($product->image)
                <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}"
                    style="height: 150px; object-fit: contain;">
            @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image"
                    style="height: 150px; object-fit: contain;">
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
