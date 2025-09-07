<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::query()->delete();


        $products = [
            [
                'name' => 'Apple iPhone 14',
                'category' => 'Electronics',
                'price' => 999.99,
                'stock_quantity' => 10,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'category' => 'Electronics',
                'price' => 899.00,
                'stock_quantity' => 15,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'HP Laptop 15"',
                'category' => 'Computers',
                'price' => 750.00,
                'stock_quantity' => 8,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Wireless Mouse',
                'category' => 'Accessories',
                'price' => 25.99,
                'stock_quantity' => 100,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Office Chair',
                'category' => 'Furniture',
                'price' => 120.00,
                'stock_quantity' => 20,
                'min_order_quantity' => 1,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
