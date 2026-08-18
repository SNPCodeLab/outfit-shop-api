<?php

namespace App\Http\Requests\Auth;

use App\Http\Response\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required_without:email|string',
            'email'    => 'required_without:username|string|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required_without' => 'Either username or email is required.',
            'email.required_without'    => 'Either username or email is required.',
            'email.email'               => 'The email address format is invalid.',
            'password.required'         => 'Password is required.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray(), 'Login credentials validation failed.')
        );
    }
}
