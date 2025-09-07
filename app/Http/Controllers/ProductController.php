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

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('category', 'like', "%$search%");
        })->orderBy('name')->paginate(10);
        return view('products.index', compact('products', 'search'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('category', 'like', "%$search%");
        })->orderBy('name')->take(20)->get();

        return response()->json($products);
    }


    // admin methods
    public function all_products()
    {
        $this->authorizeAdmin();
        $products = Product::latest()->paginate(10);
        return view('products.all_product', compact('products'));
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
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = new Product();

        $product->name = $request->name;
        $product->category = $request->category;
        $product->price = $request->price;
        $product->stock_quantity     = $request->stock_quantity;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = Str::uuid()->toString() . '-' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $fileName);
            $product->image = 'uploads/' . $fileName;
            $product->save();
        }

        $product->save();



        return redirect()->route('products.index')->with('success', 'Product created successfully.');
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
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->name = $request->name;
        $product->category = $request->category;
        $product->price = $request->price;
        $product->stock_quantity = $request->stock_quantity;

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            $file = $request->file('image');
            $fileName = Str::uuid()->toString() . '-' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $fileName);
            $product->image = 'uploads/' . $fileName;
        }

        $product->save();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }


    public function destroy(Product $product)
    {
        $this->authorizeAdmin();
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
