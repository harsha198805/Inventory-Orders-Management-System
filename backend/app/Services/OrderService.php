<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderService
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->orders->paginate($filters);
    }

    public function create(int $userId, array $items): Order
    {
        try {
            return DB::transaction(function () use ($userId, $items): Order {
                $order = $this->orders->createDraft($userId);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                return $order->load(['user', 'items.product']);
            });
        } catch (Throwable $exception) {
            Log::error('Order creation failed.', [
                'user_id' => $userId,
                'item_count' => count($items),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            
            throw $exception;
        }
    }

    public function confirm(Order $order, int $userId): Order
    {
        try {
            return DB::transaction(function () use ($order, $userId): Order {
                $order = Order::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if ($order->status === OrderStatus::Confirmed) {
                    return $order->load(['user', 'items.product']);
                }

                if ($order->status === OrderStatus::Cancelled) {
                    throw ValidationException::withMessages([
                        'status' => 'Cancelled orders cannot be confirmed.',
                    ]);
                }

                foreach ($order->items as $item) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

                    if ($product->stock_quantity < $item->quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for {$product->sku}.",
                        ]);
                    }

                    $product->decrement('stock_quantity', $item->quantity);
                }

                $oldStatus = $order->status->value;
                $order->update([
                    'status' => OrderStatus::Confirmed,
                    'confirmed_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'event' => 'order.confirmed',
                    'old_status' => $oldStatus,
                    'new_status' => OrderStatus::Confirmed->value,
                ]);

                return $order->load(['user', 'items.product']);
            });
        } catch (ValidationException $exception) {
            Log::warning('Order confirmation rejected.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'errors' => $exception->errors(),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Order confirmation failed.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function cancel(Order $order, int $userId): Order
    {
        try {
            return DB::transaction(function () use ($order, $userId): Order {
                $order = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($order->status === OrderStatus::Confirmed) {
                    throw ValidationException::withMessages([
                        'status' => 'Confirmed orders cannot be cancelled.',
                    ]);
                }

                if ($order->status === OrderStatus::Cancelled) {
                    return $order->load(['user', 'items.product']);
                }

                $oldStatus = $order->status->value;
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'event' => 'order.cancelled',
                    'old_status' => $oldStatus,
                    'new_status' => OrderStatus::Cancelled->value,
                ]);

                return $order->load(['user', 'items.product']);
            });
        } catch (ValidationException $exception) {
            Log::warning('Order cancellation rejected.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'errors' => $exception->errors(),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Order cancellation failed.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
