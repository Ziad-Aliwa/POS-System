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
                'barcode' => '10001',
                'category' => 'Electronics',
                'price' => 999.99,
                'cost_price' => 750.00,
                'stock_quantity' => 10,
                'alert_quantity' => 4,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'barcode' => '10002',
                'category' => 'Electronics',
                'price' => 899.00,
                'cost_price' => 680.00,
                'stock_quantity' => 15,
                'alert_quantity' => 5,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'HP Laptop 15"',
                'barcode' => '10003',
                'category' => 'Computers',
                'price' => 750.00,
                'cost_price' => 580.00,
                'stock_quantity' => 8,
                'alert_quantity' => 3,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Wireless Mouse',
                'barcode' => '10004',
                'category' => 'Accessories',
                'price' => 25.99,
                'cost_price' => 12.00,
                'stock_quantity' => 100,
                'alert_quantity' => 15,
                'min_order_quantity' => 1,
            ],
            [
                'name' => 'Office Chair',
                'barcode' => '10005',
                'category' => 'Furniture',
                'price' => 120.00,
                'cost_price' => 75.00,
                'stock_quantity' => 3, // intentionally low to demonstrate low-stock alert!
                'alert_quantity' => 5,
                'min_order_quantity' => 1,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
