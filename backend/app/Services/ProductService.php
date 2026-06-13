<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->products->paginate($filters);
    }

    public function create(array $data): Product
    {
        return $this->products->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->products->update($product, $data);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
