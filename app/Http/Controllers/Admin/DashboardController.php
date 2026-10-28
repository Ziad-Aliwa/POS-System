<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockProducts = Product::whereRaw('stock_quantity <= alert_quantity')->get();

        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'totalRevenue' => Order::sum('total_price'),
            'todaySales' => Order::whereDate('created_at', today())->sum('total_price'),
            'totalProfit' => Order::sum('profit'),
            'todayProfit' => Order::whereDate('created_at', today())->sum('profit'),
            'totalDiscount' => Order::sum('discount'),
            'productsCount' => Product::count(),
            'customersCount' => Customer::count(),
            'cashiersCount' => User::where('role', 'cashier')->count(),
            'cashOrdersCount' => Order::where('payment_method', 'cash')->count(),
            'cardOrdersCount' => Order::where('payment_method', 'card')->count(),
            'totalReturnsCount' => OrderReturn::count(),
            'totalRefunded' => OrderReturn::sum('total_refund'),
            'lowStockProducts' => $lowStockProducts,
            'activeShifts' => Shift::with('user')->where('status', 'open')->get(),
            'latestOrders' => Order::with(['customer', 'user'])->latest()->take(6)->get(),
            'recentMovements' => StockMovement::with(['product', 'user'])->latest()->take(5)->get(),
        ]);
    }
}
