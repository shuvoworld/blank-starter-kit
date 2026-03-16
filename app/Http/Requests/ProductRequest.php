<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();
        $sometimes = $id ? ['sometimes'] : [];

        return [
            'name' => [...$sometimes, 'required', 'string', 'max:255'],
            'sku' => [...$sometimes, 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($id)],
            'description' => [...$sometimes, 'nullable', 'string'],
            'price' => [...$sometimes, 'required', 'numeric', 'min:0'],
            'stock' => [...$sometimes, 'required', 'integer', 'min:0'],
            'category' => [...$sometimes, 'required', 'string', 'max:255'],
            'status' => [...$sometimes, 'required', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'sku.required' => 'A SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'price.numeric' => 'Price must be a valid number.',
            'stock.integer' => 'Stock must be a whole number.',
            'category.required' => 'Please select a category.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
