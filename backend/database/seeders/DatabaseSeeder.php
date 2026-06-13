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
            ['name' => 'HDMI Cable', 'sku' => 'SKU-HDMI-001', 'stock_quantity' => 18, 'reorder_level' => 8],
            ['name' => 'Webcam', 'sku' => 'SKU-WEBCAM-001', 'stock_quantity' => 7, 'reorder_level' => 5],
            ['name' => 'Headset', 'sku' => 'SKU-HEADSET-001', 'stock_quantity' => 11, 'reorder_level' => 6],
            ['name' => 'External Hard Drive', 'sku' => 'SKU-HDD-001', 'stock_quantity' => 9, 'reorder_level' => 4],
            ['name' => 'USB Hub', 'sku' => 'SKU-HUB-001', 'stock_quantity' => 5, 'reorder_level' => 7],
            ['name' => 'Monitor Arm', 'sku' => 'SKU-ARM-001', 'stock_quantity' => 13, 'reorder_level' => 5],
            ['name' => 'Desk Mat', 'sku' => 'SKU-MAT-001', 'stock_quantity' => 20, 'reorder_level' => 10],
            ['name' => 'Ethernet Cable', 'sku' => 'SKU-ETH-001', 'stock_quantity' => 6, 'reorder_level' => 6],
            ['name' => 'Bluetooth Speaker', 'sku' => 'SKU-SPEAKER-001', 'stock_quantity' => 10, 'reorder_level' => 3],
            ['name' => 'Power Adapter', 'sku' => 'SKU-ADAPTER-001', 'stock_quantity' => 14, 'reorder_level' => 9],
            ['name' => 'Tablet Stand', 'sku' => 'SKU-TABSTAND-001', 'stock_quantity' => 3, 'reorder_level' => 5],
        ])->each(fn (array $product) => Product::query()->updateOrCreate(
            ['sku' => $product['sku']],
            $product
        ));
    }
}
