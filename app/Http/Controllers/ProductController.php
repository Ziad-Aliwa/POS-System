<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    private function authorizeAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }
    }


    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $query = Product::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('category', 'like', "%$search%")
                    ->orWhere('barcode', 'like', "%$search%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->paginate(16);
        $categories = Product::select('category')->distinct()->whereNotNull('category')->pluck('category');

        return view('products.index', compact('products', 'search', 'categories', 'category'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('category', 'like', "%$search%")
                ->orWhere('barcode', 'like', "%$search%");
        })->orderBy('name')->take(20)->get();

        return response()->json($products);
    }


    // admin methods
    public function all_products(Request $request)
    {
        $this->authorizeAdmin();
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('category', 'like', "%$search%")
                ->orWhere('barcode', 'like', "%$search%");
        })->latest()->paginate(10);

        return view('products.all_product', compact('products', 'search'));
    }


    public function create()
    {
        $this->authorizeAdmin();
        return view('products.create');
    }



    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->barcode = $request->barcode;
        $product->category = $request->category;
        $product->price = $request->price;
        $product->cost_price = $request->cost_price ?? 0;
        $product->stock_quantity = $request->stock_quantity;
        $product->alert_quantity = $request->alert_quantity ?? 5;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = Str::uuid()->toString() . '-' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $fileName);
            $product->image = 'uploads/' . $fileName;
        }

        $product->save();

        return redirect()->route('products.all_products')->with('success', 'Product created successfully.');
    }



    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }




    public function edit(Product $product)
    {
        $this->authorizeAdmin();

        return view('products.edit', compact('product'));
    }


    public function update(Request $request, Product $product)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->name = $request->name;
        $product->barcode = $request->barcode;
        $product->category = $request->category;
        $product->price = $request->price;
        $product->cost_price = $request->cost_price ?? $product->cost_price;
        $product->stock_quantity = $request->stock_quantity;
        $product->alert_quantity = $request->alert_quantity ?? $product->alert_quantity;

        if ($request->hasFile('image')) {
            $rawImage = $product->getRawOriginal('image');
            if ($rawImage && file_exists(public_path($rawImage))) {
                @unlink(public_path($rawImage));
            }
            $file = $request->file('image');
            $fileName = Str::uuid()->toString() . '-' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $fileName);
            $product->image = 'uploads/' . $fileName;
        }

        $product->save();

        return redirect()->route('products.all_products')->with('success', 'Product updated successfully.');
    }


    public function destroy(Product $product)
    {
        $this->authorizeAdmin();

        $rawImage = $product->getRawOriginal('image');
        if ($rawImage && file_exists(public_path($rawImage))) {
            @unlink(public_path($rawImage));
        }

        $product->delete();

        return redirect()->route('products.all_products')->with('success', 'Product deleted successfully.');
    }
}
