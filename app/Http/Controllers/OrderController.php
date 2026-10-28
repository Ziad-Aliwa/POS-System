<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->paginate(10);
        return view('orders.index', compact('orders'));
    }

    public function export(Request $request)
    {
        $query = Order::with(['customer', 'user']);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->get();

        $fileName = 'sales_report_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Order ID',
                'Date',
                'Customer Name',
                'Customer Phone',
                'Cashier',
                'Payment Method',
                'Status',
                'Subtotal ($)',
                'Discount ($)',
                'Total Price ($)',
                'Paid Amount ($)',
                'Change Amount ($)',
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->customer->name ?? 'N/A',
                    $order->customer->phone ?? 'N/A',
                    $order->user->name ?? 'N/A',
                    strtoupper($order->payment_method ?? 'CASH'),
                    ucfirst($order->status ?? 'Completed'),
                    number_format($order->subtotal ?? $order->total_price, 2),
                    number_format($order->discount ?? 0, 2),
                    number_format($order->total_price, 2),
                    number_format($order->paid_amount ?? $order->total_price, 2),
                    number_format($order->change_amount ?? 0, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    public function thermal(Order $order)
    {
        if (
            auth()->user()->role !== 'admin' &&
            auth()->user()->role !== 'cashier'
        ) {
            abort(403, 'Unauthorized.');
        }

        $order->load(['customer', 'user', 'items.product', 'shift']);

        $qrData = "POS-INVOICE#{$order->id}|DATE:{$order->created_at->format('Y-m-d H:i')}|TOTAL:\${$order->total_price}|METHOD:{$order->payment_method}";
        $qrUrl = \App\Helpers\QRCodeHelper::url($qrData, 130);

        return view('orders.thermal', compact('order', 'qrUrl'));
    }

    public function show(Order $order)
    {
        if (!in_array(auth()->user()->role, ['admin', 'cashier'])) {
            abort(403, 'Unauthorized.');
        }

        $order->load(['customer', 'user', 'items.product', 'returns.items.product', 'shift']);

        return view('orders.show', compact('order'));
    }
}
