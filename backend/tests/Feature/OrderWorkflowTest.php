<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_order_reduces_stock(): void
    {
        $staff = $this->createUser('staff');
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
        $staff = $this->createUser('staff');
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
        $staff = $this->createUser('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 10, 'reorder_level' => 3]);

        $orderId = $this->actingAs($staff, 'sanctum')->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertCreated()->json('data.id');

        $this->actingAs($staff, 'sanctum')->postJson("/api/orders/{$orderId}/confirm")->assertOk();
        $this->actingAs($staff, 'sanctum')->postJson("/api/orders/{$orderId}/confirm")->assertOk();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 6]);
    }
}
