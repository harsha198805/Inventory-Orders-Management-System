<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = $this->user('admin');

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
        $staff = $this->user('staff');

        $this->actingAs($staff, 'sanctum')->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'MON-001',
            'stock_quantity' => 12,
            'reorder_level' => 3,
        ])->assertForbidden();
    }

    public function test_product_listing_supports_search_and_low_stock_filter(): void
    {
        $staff = $this->user('staff');
        Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 2, 'reorder_level' => 5]);
        Product::create(['name' => 'Desk', 'sku' => 'DSK-001', 'stock_quantity' => 20, 'reorder_level' => 5]);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/products?search=CAB&low_stock=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'CAB-001');
    }

    public function test_confirming_order_reduces_stock(): void
    {
        $staff = $this->user('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 10, 'reorder_level' => 3]);

        $orderId = $this->actingAs($staff, 'sanctum')->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertCreated()->json('data.id');

        $this->actingAs($staff, 'sanctum')
            ->postJson("/api/orders/{$orderId}/confirm")
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Confirmed->value);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 6,
        ]);
    }

    public function test_order_confirmation_rolls_back_when_stock_is_insufficient(): void
    {
        $staff = $this->user('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 2, 'reorder_level' => 3]);

        $orderId = $this->actingAs($staff, 'sanctum')->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertCreated()->json('data.id');

        $this->actingAs($staff, 'sanctum')
            ->postJson("/api/orders/{$orderId}/confirm")
            ->assertUnprocessable();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 2]);
        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => OrderStatus::Draft->value]);
    }

    public function test_confirming_same_order_twice_is_idempotent(): void
    {
        $staff = $this->user('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 10, 'reorder_level' => 3]);

        $orderId = $this->actingAs($staff, 'sanctum')->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertCreated()->json('data.id');

        $this->actingAs($staff, 'sanctum')->postJson("/api/orders/{$orderId}/confirm")->assertOk();
        $this->actingAs($staff, 'sanctum')->postJson("/api/orders/{$orderId}/confirm")->assertOk();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 6]);
    }

    public function test_reports_return_low_stock_and_daily_summary(): void
    {
        $staff = $this->user('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 5, 'reorder_level' => 5]);
        $order = Order::create([
            'user_id' => $staff->id,
            'order_number' => 'ORD-TEST',
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 3]);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/reports/low-stock')
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'CAB-001');

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/reports/daily-orders')
            ->assertOk()
            ->assertJsonPath('data.0.order_count', 1)
            ->assertJsonPath('data.0.total_items', 3);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => "{$role}@example.com",
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
