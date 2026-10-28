<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    protected StockService $stockService;
    protected ShiftService $shiftService;

    public function __construct(StockService $stockService, ShiftService $shiftService)
    {
        $this->stockService = $stockService;
        $this->shiftService = $shiftService;
    }

    /**
     * Process checkout for the user's active cart.
     */
    public function processCheckout(
        User $user,
        int $customerId,
        string $paymentMethod,
        float $discount = 0.0,
        ?float $paidAmount = null,
        ?string $notes = null
    ): Order {
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $items = $cart->items()->with('product')->get();

        if ($items->isEmpty()) {
            throw new Exception('Cart is empty.');
        }

        // Validate stock
        foreach ($items as $item) {
            if ($item->quantity > $item->product->stock_quantity) {
                throw new Exception("Not enough stock for {$item->product->name}. Available: {$item->product->stock_quantity}");
            }
        }

        // Calculate subtotal, total cost, and total price
        $subtotal = 0.0;
        $totalCost = 0.0;
        foreach ($items as $item) {
            $itemSubtotal = $item->product->price * $item->quantity;
            $itemCost = ($item->product->cost_price ?? 0) * $item->quantity;
            $subtotal += $itemSubtotal;
            $totalCost += $itemCost;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $finalTotal = max(0.0, $subtotal - $discount);
        $profit = max(0.0, $finalTotal - $totalCost);

        $changeAmount = 0.0;
        if ($paymentMethod === 'cash') {
            $paid = $paidAmount ?? $finalTotal;
            if ($paid < $finalTotal) {
                throw new Exception("Paid amount (\${$paid}) is less than total payable (\${$finalTotal}).");
            }
            $changeAmount = max(0.0, $paid - $finalTotal);
        } else {
            $paid = $finalTotal;
            $changeAmount = 0.0;
        }

        // Active shift if any
        $activeShift = $this->shiftService->getActiveShift($user->id);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'shift_id' => $activeShift?->id,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_price' => $finalTotal,
                'profit' => $profit,
                'paid_amount' => $paid,
                'change_amount' => $changeAmount,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price_per_unit' => $item->product->price,
                    'cost_price' => $item->product->cost_price ?? 0,
                ]);

                // Record stock movement (sale) & decrement stock
                $this->stockService->recordMovement(
                    $item->product,
                    -$item->quantity,
                    'sale',
                    $user->id,
                    'order',
                    $order->id,
                    "Order #{$order->id} sale"
                );
            }

            // Update shift if present
            if ($activeShift) {
                $this->shiftService->recordSale($activeShift, $order);
            }

            // Empty cart
            $cart->items()->delete();

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
