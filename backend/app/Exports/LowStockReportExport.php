<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LowStockReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $products)
    {
    }

    public function collection(): Collection
    {
        return $this->products->map(fn ($product): array => [
            'product' => $product->name,
            'sku' => $product->sku,
            'stock' => $product->stock_quantity,
            'reorder' => $product->reorder_level,
        ]);
    }

    public function headings(): array
    {
        return ['Product', 'SKU', 'Stock', 'Reorder'];
    }
}
