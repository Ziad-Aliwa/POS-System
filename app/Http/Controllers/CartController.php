<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    // View cart contents
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $items = $cart->items()->with('product')->get();
        return view('cart.index', compact('cart', 'items'));
    }

    public function apiData()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $items = $cart->items()->with('product')->get();
        $subtotal = 0;
        $itemsArray = [];

        foreach ($items as $item) {
            $lineTotal = $item->product->price * $item->quantity;
            $subtotal += $lineTotal;
            $itemsArray[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'barcode' => $item->product->barcode,
                'price' => (float) $item->product->price,
                'quantity' => (int) $item->quantity,
                'stock' => (int) $item->product->stock_quantity,
                'subtotal' => (float) $lineTotal,
                'image' => $item->product->image,
            ];
        }

        return response()->json([
            'items' => $itemsArray,
            'subtotal' => (float) $subtotal,
            'count' => (int) $items->sum('quantity'),
        ]);
    }

    public function clear()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            $cart->items()->delete();
        }
        return response()->json(['status' => 'success', 'message' => 'Cart cleared.']);
    }


    // Add product to cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $product = Product::find($request->product_id);
                    // dd($attribute);
                    if ($product && $value > $product->stock_quantity) {
                        $fail("The quantity cannot be greater than the available stock ({$product->stock_quantity}).");
                    }
                },
            ],
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();
        if ($item) {

            if ($product->stock_quantity < $request->quantity + ($item->quantity ?? 0)) {
                return redirect()->back()->with('error', 'Not enough stock available.');
            }
        }

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }

        return redirect()->back()->with('success', "{$product->name} added to your cart.");
    }


    //  Update quantity
    public function update(Request $request, CartItem $item)
    {
        $this->authorizeCartItem($item);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $item->product;

        if ($request->quantity > $product->stock_quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requested quantity exceeds available stock.',
            ], 422);
        }

        $item->update(['quantity' => $request->quantity]);

        return response()->json([
            'status' => 'success',
            'price' => $product->price,
        ]);
    }



    //  Remove a product from the cart
    public function remove(CartItem $item)
    {
        $this->authorizeCartItem($item);

        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    private function authorizeCartItem(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }



    public function addByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', trim($request->barcode))->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product with barcode [' . $request->barcode . '] not found.',
            ], 404);
        }

        if ($product->stock_quantity < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product [' . $product->name . '] is out of stock.',
            ], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $currentQty = $item ? $item->quantity : 0;
        if ($product->stock_quantity < $currentQty + 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot add more. Available stock for [' . $product->name . '] is ' . $product->stock_quantity . '.',
            ], 422);
        }

        if ($item) {
            $item->quantity += 1;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        $totalItems = $cart->items()->sum('quantity');

        return response()->json([
            'status' => 'success',
            'message' => 'Added ' . $product->name . ' to cart!',
            'cart_count' => $totalItems,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ],
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,card,transfer',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ], [
            'customer_id.required' => 'Please select a customer before checking out.',
            'customer_id.exists' => 'The selected customer is not valid.',
        ]);

        try {
            $order = $this->checkoutService->processCheckout(
                Auth::user(),
                (int) $request->customer_id,
                $request->payment_method,
                floatval($request->input('discount', 0)),
                $request->filled('paid_amount') ? floatval($request->paid_amount) : null,
                $request->notes
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Order #{$order->id} placed successfully!",
                    'order_id' => $order->id,
                    'thermal_url' => route('orders.thermal', $order->id),
                    'invoice_url' => route('orders.print', $order->id),
                    'show_url' => route('orders.show', $order->id),
                ]);
            }

            return redirect()->route('orders.show', $order->id)
                ->with('success', "Order #{$order->id} placed successfully! Payment Method: " . ucfirst($order->payment_method));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }



    public function count()
    {
        $count = 0;

        if (auth()->check()) {
            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
            $count = $cart->items()->sum('quantity');
        }

        return response()->json(['count' => $count]);
    }
    
}
