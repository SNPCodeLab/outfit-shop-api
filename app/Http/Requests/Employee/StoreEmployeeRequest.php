<?php

namespace App\Http\Requests\Employee;

use App\Http\Response\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_name' => 'required|string|max:150',
            'gender'        => 'nullable|string|in:MALE,FEMALE,OTHER',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'required|email|unique:employees,email',
            'position'      => 'nullable|string|max:100',
            'username'      => 'required|string|min:4|max:50|unique:employees,username',
            'password'      => 'required|string|min:8',
            'role'          => 'required|string|in:ADMIN,MANAGER,CASHIER,STAFF',
            'status'        => 'nullable|string|in:ACTIVE,INACTIVE',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_name.required' => 'Employee full name is required.',
            'email.required'         => 'Employee email address is required.',
            'email.email'            => 'The email address format is invalid.',
            'email.unique'           => 'This email address is already registered to another employee.',
            'username.required'      => 'Username is required.',
            'username.unique'        => 'This username is already taken.',
            'username.min'           => 'Username must be at least 4 characters.',
            'password.required'      => 'Password is required.',
            'password.min'           => 'Password must be at least 8 characters.',
            'role.required'          => 'Employee role is required.',
            'role.in'                => 'Role must be one of: ADMIN, MANAGER, CASHIER, STAFF.',
            'status.in'              => 'Status must be ACTIVE or INACTIVE.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray(), 'Employee registration data validation failed.')
        );
    }
}
