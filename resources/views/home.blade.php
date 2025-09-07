@extends('layouts.layout')

@section('title', 'Home')

@section('content')
    <div class="text-center">
        <h1 class="mb-4">Welcome, {{ auth()->user()->name }}!</h1>
        <h5 class="mb-4">You are logged in as <strong>{{ ucfirst(auth()->user()->role) }}</strong></h5>

        <div class="row justify-content-center">
            @if (auth()->user()->role === 'admin')
                <div class="col-md-3">
                    <a href="{{-- route('customers.index') --}}" class="btn btn-outline-primary w-100 mb-2">Manage Customers</a>
                    <a href="{{-- route('products.index') --}}" class="btn btn-outline-secondary w-100 mb-2">Manage Products</a>
                    <a href="{{-- route('suppliers.index') --}}" class="btn btn-outline-success w-100 mb-2">Manage Suppliers</a>
                </div>
            @elseif(auth()->user()->role === 'cashier')
                <div class="col-md-3">
                    <a href="{{-- route('orders.create') --}}" class="btn btn-outline-primary w-100 mb-2">Create Order</a>
                    <a href="{{-- route('customers.index') --}}" class="btn btn-outline-secondary w-100 mb-2">Search Customers</a>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100 mb-2">Show Customer</a>
                    <a href="{{ route('customers.create') }}" class="btn btn-outline-secondary w-100 mb-2">Add Customer</a>
                </div>
            @endif
        </div>
    </div>
@endsection
