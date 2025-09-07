<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        Order::query()->delete();
        OrderItem::query()->delete();

        $customerIds = Customer::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $cashier = User::where('role', 'cashier')->first();

        if (!$cashier || empty($customerIds) || empty($productIds)) {
            echo "Make sure you have at least 1 cashier, 1 customer and 1 product.\n";
            return;
        }

        for ($i = 0; $i < 5; $i++) {
            DB::beginTransaction();

            try {
                $order = Order::create([
                    'customer_id' => $customerIds[array_rand($customerIds)],
                    'user_id' => $cashier->id,
                    'total' => 0,
                    'status' => 'completed',
                    'created_at' => now()->subDays(rand(0, 10)),
                ]);

                $total = 0;

                foreach (array_rand($productIds, rand(1, 3)) as $index) {
                    $product = Product::find($productIds[$index]);
                    $quantity = rand(1, 3);
                    $subtotal = $quantity * $product->price;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                    ]);

                    $total += $subtotal;
                }

                $order->update(['total' => $total]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                echo "Error: " . $e->getMessage();
            }
        }
    }
}
