<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
            ]
        );

        collect([
            ['name' => 'Wireless Mouse', 'sku' => 'SKU-MOUSE-001', 'stock_quantity' => 24, 'reorder_level' => 10],
            ['name' => 'Mechanical Keyboard', 'sku' => 'SKU-KEY-001', 'stock_quantity' => 8, 'reorder_level' => 5],
            ['name' => 'USB-C Cable', 'sku' => 'SKU-CABLE-001', 'stock_quantity' => 4, 'reorder_level' => 12],
            ['name' => 'Laptop Stand', 'sku' => 'SKU-STAND-001', 'stock_quantity' => 15, 'reorder_level' => 6],
        ])->each(fn (array $product) => Product::query()->updateOrCreate(
            ['sku' => $product['sku']],
            $product
        ));
    }
}
