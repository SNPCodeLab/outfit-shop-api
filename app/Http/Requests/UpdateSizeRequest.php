<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'size_name' => 'sometimes|string|min:1|max:20',
            'size_order' => 'sometimes|integer|min:0',
            'size_code' => 'nullable|string|max:30',
        ];
    }
}
