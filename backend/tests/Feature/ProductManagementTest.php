<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'MON-001',
            'stock_quantity' => 12,
            'reorder_level' => 3,
        ]);

        $response->assertCreated()->assertJsonPath('data.sku', 'MON-001');
        $this->assertDatabaseHas('products', ['sku' => 'MON-001']);
    }

    public function test_staff_cannot_create_product(): void
    {
        $staff = $this->createUser('staff');

        $this->actingAs($staff, 'sanctum')->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'MON-001',
            'stock_quantity' => 12,
            'reorder_level' => 3,
        ])->assertForbidden();
    }

    public function test_product_listing_supports_search_and_low_stock_filter(): void
    {
        $staff = $this->createUser('staff');
        Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 2, 'reorder_level' => 5]);
        Product::create(['name' => 'Desk', 'sku' => 'DSK-001', 'stock_quantity' => 20, 'reorder_level' => 5]);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/products?search=CAB&low_stock=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'CAB-001');
    }
}
