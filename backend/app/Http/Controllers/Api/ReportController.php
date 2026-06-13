<?php

namespace App\Http\Controllers\Api;

use App\Exports\DailyOrdersReportExport;
use App\Exports\LowStockReportExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function exportLowStock(): BinaryFileResponse
    {
        return Excel::download(new LowStockReportExport($this->reports->lowStock()), 'low-stock-report.xlsx');
    }

    public function exportDailyOrders(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return Excel::download(new DailyOrdersReportExport($this->reports->dailyOrders($data['date'] ?? null)), 'daily-orders-report.xlsx');
    }
}
