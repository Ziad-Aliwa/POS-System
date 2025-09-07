<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::query()->delete();

        $customers = [
            [
                'name' => 'Mohamed Salah',
                'phone' => '01012345678',
                'email' => 'salah@example.com',
                'address' => 'Cairo, Egypt',
            ],
            [
                'name' => 'Ahmed Ali',
                'phone' => '01122334455',
                'email' => 'ahmed@example.com',
                'address' => 'Giza, Egypt',
            ],
            [
                'name' => 'moataz Youssef',
                'phone' => '01234567890',
                'email' => 'moataz@example.com',
                'address' => 'Alexandria, Egypt',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
