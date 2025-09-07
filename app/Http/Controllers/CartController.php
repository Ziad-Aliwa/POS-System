<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // View cart contents
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $items = $cart->items()->with('product')->get();
        return view('cart.index', compact('cart', 'items'));
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



    public function process(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',

        ], [
            'customer_id.required' => 'Please select a customer before checking out.',
            'customer_id.exists' => 'The selected customer is not valid.',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $items = $cart->items()->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        $total = 0;
        foreach ($items as $item) {
            $price = $item->product->price;
            $subtotal = $price * $item->quantity;
            $total += $subtotal;
        }
        
        foreach ($items as $item) {
            if ($item->quantity > $item->product->stock_quantity) {
                return redirect()->route('cart.index')->with('error', "Not enough stock for product: {$item->product->name}");
            }
        }
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $request->customer_id,
            'total_price' => $total,
        ]);


        $total = 0;

        foreach ($items as $item) {
            $price = $item->product->price;
            $subtotal = $price * $item->quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price_per_unit' => $item->product->price
            ]);

            $total += $subtotal;

            $item->product->decrement('stock_quantity', $item->quantity);
        }

        $order->update(['total' => $total]);

        $cart->items()->delete();

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
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
