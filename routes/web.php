<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\HeldOrderController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\StockMovementController;

Route::get('/', [ProductController::class, 'index'])->name('home')->middleware('auth');

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('store.register');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/products/search', [ProductController::class, 'search'])->name('products.search')->middleware('checkrole:admin,cashier');

Route::prefix('products')->middleware('checkrole:admin')->name('products.')->group(function () {
    Route::get('/all/products', [ProductController::class, 'all_products'])->name('all_products');
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show')->whereNumber('product');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit')->whereNumber('product');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update')->whereNumber('product');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy')->whereNumber('product');
});

Route::middleware(['checkrole:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('customers', CustomerController::class);
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/inventory/movements', [StockMovementController::class, 'index'])->name('inventory.movements');
});

Route::middleware(['checkrole:admin,cashier'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/api-data', [CartController::class, 'apiData'])->name('cart.api.data');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-by-barcode', [CartController::class, 'addByBarcode'])->name('cart.add.barcode');
    Route::put('/cart/item/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/checkout', [CartController::class, 'process'])->name('checkout.process');
    Route::post('/customers/store-ajax', [CustomerController::class, 'storeAjax'])->name('customers.store.ajax');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy')->middleware('auth');
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('/orders/{order}/thermal', [OrderController::class, 'thermal'])->name('orders.thermal');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Shift management for cashiers
    Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shifts/{shift}/report', [ShiftController::class, 'report'])->name('shifts.report');

    // Held / Parked Orders
    Route::get('/held-orders', [HeldOrderController::class, 'index'])->name('held.index');
    Route::post('/held-orders', [HeldOrderController::class, 'store'])->name('held.store');
    Route::post('/held-orders/{heldOrder}/recall', [HeldOrderController::class, 'recall'])->name('held.recall');
    Route::delete('/held-orders/{heldOrder}', [HeldOrderController::class, 'destroy'])->name('held.destroy');

    // Returns & Refunds
    Route::get('/returns', [OrderReturnController::class, 'index'])->name('returns.index');
    Route::get('/orders/{order}/return', [OrderReturnController::class, 'create'])->name('orders.return.create');
    Route::post('/orders/{order}/return', [OrderReturnController::class, 'store'])->name('orders.return.store');
});

