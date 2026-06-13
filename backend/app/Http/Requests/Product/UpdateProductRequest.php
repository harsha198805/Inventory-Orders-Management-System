<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'reorder_level' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
