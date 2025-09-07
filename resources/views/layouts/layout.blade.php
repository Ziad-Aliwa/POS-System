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
        <div class="container">
            <a class="navbar-brand" href="/">POS System</a>
            @if (auth()->check() && in_array(auth()->user()->role, ['admin' , 'cashier']))
                <a href="{{ route('orders.index') }}" class="nav-link text-white ms-3">Orders</a>
            @endif

            @if (auth()->check() && in_array(auth()->user()->role, ['admin']))
                <a href="{{ route('products.all_products') }}" class="nav-link text-white ms-3">Products</a>
                <a href="{{ route('customers.index') }}" class="nav-link text-white ms-3">Customers</a>
            @endif

            <div class="d-flex ms-auto">
                @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'cashier']))
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a href="{{ route('cart.index') }}" class="nav-link text-white">
                                🛒 Cart <span id="cart-count" class="badge bg-light text-dark">0</span>
                            </a>
                        </li>
                    </ul>
                @endif

                &nbsp;&nbsp;&nbsp;
                @if (auth()->check())
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            Logout ({{ auth()->user()->name }})
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm">Register</a>
                @endif
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

</body>
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

</html>
