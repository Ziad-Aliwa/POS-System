<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'POS System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="/">🛍️ POS System</a>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @if (auth()->check() && in_array(auth()->user()->role, ['admin']))
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a>
                        </li>
                    @endif

                    @if (auth()->check() && in_array(auth()->user()->role, ['admin' , 'cashier']))
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link text-white fw-bold text-warning">⚡ POS Terminal</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orders.index') }}" class="nav-link text-white">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('returns.index') }}" class="nav-link text-white">Returns</a>
                        </li>
                    @endif

                    @if (auth()->check() && in_array(auth()->user()->role, ['admin']))
                        <li class="nav-item">
                            <a href="{{ route('products.all_products') }}" class="nav-link text-white">Products</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customers.index') }}" class="nav-link text-white">Customers</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('shifts.index') }}" class="nav-link text-white">Shifts</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.movements') }}" class="nav-link text-white">Stock Audit</a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center ms-auto">
                    @if (auth()->check())
                        <span class="badge bg-secondary me-3">{{ ucfirst(auth()->user()->role) }}: {{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>


    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch("{{ route('cart.count') }}")
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.count;
                    }
                });
        });
    </script>
    @yield('scripts')
</body>
</html>
