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
            <label class="form-label">Barcode (optional)</label>
            <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                value="{{ old('barcode', $product->barcode) }}" placeholder="e.g. 10001">
            @error('barcode')
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

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Selling Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                    value="{{ old('price', $product->price) }}" required>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Cost Price ($) (for Profit calculations)</label>
                <input type="number" step="0.01" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror"
                    value="{{ old('cost_price', $product->cost_price ?? 0) }}">
                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                    value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                @error('stock_quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Low Stock Alert Threshold</label>
                <input type="number" name="alert_quantity" class="form-control @error('alert_quantity') is-invalid @enderror"
                    value="{{ old('alert_quantity', $product->alert_quantity ?? 5) }}">
                @error('alert_quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
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
