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

        $paymentMethods = ['cash', 'card', 'transfer'];

        for ($i = 0; $i < 5; $i++) {
            DB::beginTransaction();

            try {
                $method = $paymentMethods[array_rand($paymentMethods)];
                $order = Order::create([
                    'customer_id' => $customerIds[array_rand($customerIds)],
                    'user_id' => $cashier->id,
                    'payment_method' => $method,
                    'status' => 'completed',
                    'subtotal' => 0,
                    'discount' => rand(0, 1) ? rand(5, 20) : 0,
                    'total_price' => 0,
                    'paid_amount' => 0,
                    'change_amount' => 0,
                    'created_at' => now()->subDays(rand(0, 10)),
                ]);

                $subtotal = 0;
                $totalCost = 0;

                foreach ((array) array_rand($productIds, rand(1, 3)) as $index) {
                    $product = Product::find($productIds[$index]);
                    $quantity = rand(1, 3);
                    $itemSubtotal = $quantity * $product->price;
                    $itemCost = ($product->cost_price ?? ($product->price * 0.7)) * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price_per_unit' => $product->price,
                        'cost_price' => $product->cost_price ?? ($product->price * 0.7),
                    ]);

                    $subtotal += $itemSubtotal;
                    $totalCost += $itemCost;
                }

                $discount = min($order->discount, $subtotal);
                $finalTotal = max(0, $subtotal - $discount);
                $profit = max(0, $finalTotal - $totalCost);
                $paid = $method === 'cash' ? ($finalTotal + rand(0, 10)) : $finalTotal;
                $change = $paid - $finalTotal;

                $order->update([
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total_price' => $finalTotal,
                    'profit' => $profit,
                    'paid_amount' => $paid,
                    'change_amount' => $change,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                echo "Error: " . $e->getMessage();
            }
        }
    }
}
