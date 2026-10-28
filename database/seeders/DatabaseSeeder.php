<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin Manager',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Default Cashier User
        \App\Models\User::firstOrCreate(
            ['email' => 'cashier@pos.com'],
            [
                'name' => 'Main Cashier',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        $this->call([
            ProductSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
