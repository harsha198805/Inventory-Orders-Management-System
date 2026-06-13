<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(private readonly ReportRepository $reports)
    {
    }

    public function lowStock(): Collection
    {
        return $this->reports->lowStock();
    }

    public function dailyOrders(?string $date = null): Collection
    {
        return $this->reports->dailyOrders($date);
    }
}
