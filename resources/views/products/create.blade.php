@extends('layouts.layout')

@section('title', 'Add Product')

@section('content')
    <h2 class="mb-4">Add Product</h2>
<div >
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                   value="{{ old('category') }}" required>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Price ($)</label>
            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price') }}" required>
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                   value="{{ old('stock_quantity') }}" required>
            @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Image (optional)</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Add Product</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
        
    </form>
    </div>
@endsection
