<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function lowStock(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->reports->lowStock());
    }

    public function dailyOrders(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return response()->json([
            'data' => $this->reports->dailyOrders($data['date'] ?? null),
        ]);
    }
}
