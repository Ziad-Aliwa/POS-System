<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Process return of specific order items.
     * $returnItems: array of ['order_item_id' => int, 'quantity' => int]
     */
    public function processReturn(
        Order $order,
        User $user,
        array $itemsToReturn,
        string $refundMethod,
        ?string $reason = null
    ): OrderReturn {
        if ($order->status === 'returned') {
            throw new Exception('This order has already been completely refunded.');
        }

        DB::beginTransaction();
        try {
            $totalRefund = 0.0;
            $itemsData = [];

            foreach ($itemsToReturn as $itemInput) {
                $orderItemId = (int) $itemInput['order_item_id'];
                $qty = (int) $itemInput['quantity'];

                if ($qty <= 0) continue;

                $orderItem = OrderItem::with('product')->where('order_id', $order->id)->where('id', $orderItemId)->firstOrFail();

                // Check already returned quantity
                $alreadyReturned = OrderReturnItem::where('order_item_id', $orderItem->id)->sum('quantity');
                $maxReturnable = $orderItem->quantity - $alreadyReturned;

                if ($qty > $maxReturnable) {
                    throw new Exception("Cannot return {$qty} of {$orderItem->product->name}. Maximum returnable: {$maxReturnable}");
                }

                $refundAmount = $orderItem->price_per_unit * $qty;
                $totalRefund += $refundAmount;

                $itemsData[] = [
                    'order_item' => $orderItem,
                    'quantity' => $qty,
                    'refund_price' => $orderItem->price_per_unit,
                ];
            }

            if (empty($itemsData)) {
                throw new Exception('No valid items selected for return.');
            }

            $orderReturn = OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'total_refund' => $totalRefund,
                'refund_method' => $refundMethod,
                'reason' => $reason,
            ]);

            foreach ($itemsData as $data) {
                OrderReturnItem::create([
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $data['order_item']->product_id,
                    'order_item_id' => $data['order_item']->id,
                    'quantity' => $data['quantity'],
                    'refund_price' => $data['refund_price'],
                ]);

                // Restock the product and record stock movement
                $this->stockService->recordMovement(
                    $data['order_item']->product,
                    $data['quantity'],
                    'return',
                    $user->id,
                    'return',
                    $orderReturn->id,
                    "Return for Order #{$order->id}"
                );
            }

            DB::commit();
            return $orderReturn;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
