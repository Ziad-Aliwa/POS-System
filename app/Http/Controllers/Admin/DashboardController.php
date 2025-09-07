<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;

class DashboardController extends Controller
{
   public function index()
    {
        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'totalRevenue' => Order::sum('total_price'),
            'productsCount' => Product::count(),
            'customersCount' => Customer::count(),
            'cashiersCount' => User::where('role', 'cashier')->count(),
            'latestOrders' => Order::latest()->take(5)->get()
        ]);
    }
}
