<?php

namespace App\Http\Requests\Sale;

use App\Http\Response\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,customer_id',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:CASH,CARD,QR,ABA,BAKONG,GIFT_CARD',
            'payment_amount' => 'nullable|numeric|min:0',
            'overall_discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'idempotency_key' => 'nullable|string|max:64',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required for checkout.',
            'items.min' => 'At least one item is required for checkout.',
            'items.*.variant_id.required' => 'Each item must specify a valid variant ID.',
            'items.*.variant_id.exists' => 'One or more product variants were not found in the catalog.',
            'items.*.quantity.required' => 'Each item must have a quantity.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
            'payment_method.in' => 'Payment method must be one of: CASH, CARD, QR, ABA, BAKONG, GIFT_CARD.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray(), 'Checkout request validation failed.')
        );
    }
}
