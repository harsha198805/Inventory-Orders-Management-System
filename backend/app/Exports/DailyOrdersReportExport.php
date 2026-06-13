<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyOrdersReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row): array => [
            'date' => $row->order_date,
            'orders' => $row->order_count,
            'total_items' => $row->total_items,
        ]);
    }

    public function headings(): array
    {
        return ['Date', 'Orders', 'Total Items'];
    }
}
