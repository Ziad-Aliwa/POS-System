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
            <label class="form-label">Barcode (optional, e.g. 10006)</label>
            <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                   value="{{ old('barcode') }}" placeholder="Scan or enter unique product barcode">
            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                   value="{{ old('category') }}" required>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Selling Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price') }}" required>
                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Cost Price ($) (for Profit calculations)</label>
                <input type="number" step="0.01" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror"
                       value="{{ old('cost_price', '0.00') }}">
                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Initial Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                       value="{{ old('stock_quantity', '0') }}" required>
                @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Low Stock Alert Threshold</label>
                <input type="number" name="alert_quantity" class="form-control @error('alert_quantity') is-invalid @enderror"
                       value="{{ old('alert_quantity', '5') }}">
                <small class="text-muted">Trigger warning when stock reaches this quantity.</small>
                @error('alert_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
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
