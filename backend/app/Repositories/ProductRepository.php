<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(($filters['low_stock'] ?? null) !== null, function ($query) use ($filters): void {
                if (filter_var($filters['low_stock'], FILTER_VALIDATE_BOOL)) {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level');
                }
            })
            ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_dir'] ?? 'asc')
            ->paginate((int) ($filters['per_page'] ?? 10));
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function lowStock(): Collection
    {
        return Product::query()
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->get();
    }
}
