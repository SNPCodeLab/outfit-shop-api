<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Response\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,category_id',
            'product_name' => 'required|string|max:150',
            'brand' => 'nullable|string|max:100',
            'brand_id' => 'nullable|exists:brands,brand_id',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:2048',
            'image_public_id' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE,DRAFT',
            'material_fabric' => 'nullable|string|max:200',
            'gender' => 'nullable|string|max:50',
            'season_collection' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'size_id' => 'nullable|exists:clothing_sizes,size_id',
            'color_id' => 'nullable|exists:colors,color_id',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'A category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'product_name.required' => 'Product name is required.',
            'product_name.max' => 'Product name must not exceed 150 characters.',
            'status.in' => 'Status must be ACTIVE or INACTIVE.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray(), 'Product data validation failed.')
        );
    }
}
