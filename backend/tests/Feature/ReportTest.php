<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_return_low_stock_and_daily_summary(): void
    {
        $staff = $this->createUser('staff');
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

    public function test_report_exports_return_excel_downloads(): void
    {
        $staff = $this->createUser('staff');
        $product = Product::create(['name' => 'Cable', 'sku' => 'CAB-001', 'stock_quantity' => 5, 'reorder_level' => 5]);
        $order = Order::create([
            'user_id' => $staff->id,
            'order_number' => 'ORD-EXPORT',
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $lowStockExport = $this->actingAs($staff, 'sanctum')
            ->get('/api/reports/low-stock/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertStringContainsString('low-stock-report.xlsx', $lowStockExport->headers->get('content-disposition'));

        $dailyOrdersExport = $this->actingAs($staff, 'sanctum')
            ->get('/api/reports/daily-orders/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertStringContainsString('daily-orders-report.xlsx', $dailyOrdersExport->headers->get('content-disposition'));
    }
}
