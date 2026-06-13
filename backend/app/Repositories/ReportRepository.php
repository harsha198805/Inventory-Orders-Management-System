<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    public function lowStock(): Collection
    {
        return Product::query()
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->get();
    }

    public function dailyOrders(?string $date = null): Collection
    {
        return Order::query()
            ->select([
                DB::raw('date(orders.confirmed_at) as order_date'),
                DB::raw('count(distinct orders.id) as order_count'),
                DB::raw('coalesce(sum(order_items.quantity), 0) as total_items'),
            ])
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Confirmed->value)
            ->whereNotNull('orders.confirmed_at')
            ->when($date, fn ($query) => $query->whereDate('orders.confirmed_at', $date))
            ->groupBy(DB::raw('date(orders.confirmed_at)'))
            ->orderByDesc('order_date')
            ->get()
            ->map(function ($row) {
                $row->order_count = (int) $row->order_count;
                $row->total_items = (int) $row->total_items;

                return $row;
            });
    }
}
