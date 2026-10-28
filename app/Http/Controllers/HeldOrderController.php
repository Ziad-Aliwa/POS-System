<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\HeldOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HeldOrderController extends Controller
{
    public function index()
    {
        $heldOrders = HeldOrder::with('customer')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($heldOrders);
    }

    /**
     * Park (hold) the current cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference_note' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty. Nothing to hold.',
            ], 422);
        }

        $items = $cart->items()->with('product')->get();
        $cartData = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item->product->price * $item->quantity;
            $subtotal += $itemSubtotal;
            $cartData[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'barcode' => $item->product->barcode,
                'price' => (float) $item->product->price,
                'quantity' => (int) $item->quantity,
            ];
        }

        $discount = floatval($request->discount ?? 0);
        $total = max(0, $subtotal - $discount);

        DB::beginTransaction();
        try {
            $heldOrder = HeldOrder::create([
                'user_id' => $user->id,
                'customer_id' => $request->customer_id,
                'reference_note' => $request->reference_note ?: ('Hold #' . (HeldOrder::where('user_id', $user->id)->count() + 1)),
                'cart_data' => $cartData,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
            ]);

            // Clear current cart items
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order held successfully.',
                'held_order' => $heldOrder,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recall a held order back into the cart.
     */
    public function recall(HeldOrder $heldOrder)
    {
        if ($heldOrder->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::beginTransaction();
        try {
            // Clear whatever is in current cart or merge
            $cart->items()->delete();

            foreach ($heldOrder->cart_data as $item) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $customerId = $heldOrder->customer_id;
            $discount = $heldOrder->discount;

            // Delete held order once recalled
            $heldOrder->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order recalled into cart.',
                'customer_id' => $customerId,
                'discount' => $discount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HeldOrder $heldOrder)
    {
        if ($heldOrder->user_id !== Auth::id()) {
            abort(403);
        }

        $heldOrder->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Held order discarded.',
        ]);
    }
}
