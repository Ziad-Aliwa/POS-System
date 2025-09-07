@extends('layouts.layout')

@section('title', 'Admin Dashboard')

@section('content')
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Total Orders</h5>
                    <p class="fs-4">{{ $ordersCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <p class="fs-4">${{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Products</h5>
                    <p class="fs-4">{{ $productsCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Customers</h5>
                    <p class="fs-4">{{ $customersCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Orders --}}
    <div class="card">
        <div class="card-header">Latest Orders</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>${{ number_format($order->total_price, 2) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No recent orders</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
