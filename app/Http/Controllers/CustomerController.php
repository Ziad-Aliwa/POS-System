<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        })->latest()->paginate(10);

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:customers,email',
            'address' => 'nullable|string|max:255',
        ]);

        Customer::create($request->only('name', 'phone', 'email', 'address'));

        return redirect()->route('customers.index')->with('success', 'Customer added successfully!');
    }


    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only('name', 'phone', 'email', 'address'));

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }



    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create($request->only(['name', 'phone']));

        return response()->json([
            'status' => 'success',
            'customer' => $customer,
        ]);
    }

    public function destroy(Product $product){
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        $product->delete();

        return redirect()->route('customers.index')->with('success', 'Customer delete successfully!');

    }
}
