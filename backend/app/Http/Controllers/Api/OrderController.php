<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:draft,confirmed,cancelled'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return OrderResource::collection($this->orders->paginate($filters));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        return (new OrderResource($this->orders->create($request->user()->id, $request->validated('items'))))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['user', 'items.product']));
    }

    public function confirm(Request $request, Order $order): OrderResource
    {
        return new OrderResource($this->orders->confirm($order, $request->user()->id));
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        return new OrderResource($this->orders->cancel($order, $request->user()->id));
    }
}
