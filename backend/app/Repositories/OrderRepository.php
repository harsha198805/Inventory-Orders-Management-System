<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Order::query()
            ->with(['user', 'items.product'])
            ->withCount('items')
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 10));
    }

    public function createDraft(int $userId): Order
    {
        return Order::create([
            'user_id' => $userId,
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            'status' => OrderStatus::Draft,
        ]);
    }
}
