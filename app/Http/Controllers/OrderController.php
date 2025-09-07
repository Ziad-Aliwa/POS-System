<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('customer')->latest()->paginate(10);
        return view('orders.index', compact('orders'));
    }


    public function destroy(Order $order)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    public function print(Order $order)
    {
        if (
            auth()->user()->role !== 'admin' &&
            auth()->user()->role !== 'cashier'
        ) {
            abort(403, 'Unauthorized.');
        }

        $pdf = Pdf::loadView('orders.invoice', compact('order'));

        return $pdf->stream('invoice_' . $order->id . '.pdf');
    }

    public function show(Order $order)
    {
        if (!in_array(auth()->user()->role, ['admin', 'cashier'])) {
            abort(403, 'Unauthorized.');
        }

        return view('orders.show', compact('order'));
    }
}
