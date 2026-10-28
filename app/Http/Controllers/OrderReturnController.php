<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderReturnController extends Controller
{
    protected ReturnService $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    public function index()
    {
        $returns = OrderReturn::with(['order.customer', 'user', 'items.product'])->latest()->paginate(15);
        return view('returns.index', compact('returns'));
    }

    public function create(Order $order)
    {
        $order->load(['items.product', 'customer', 'returns.items']);
        return view('returns.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:0',
            'refund_method' => 'required|in:cash,card,transfer',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $orderReturn = $this->returnService->processReturn(
                $order,
                Auth::user(),
                $request->items,
                $request->refund_method,
                $request->reason
            );

            return redirect()->route('orders.show', $order->id)
                ->with('success', "Return #{$orderReturn->id} processed successfully! Refunded \${$orderReturn->total_refund}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
