<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Models\Employee;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();

        if ($search = $request->input('q') ?? $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'ILIKE', "%{$search}%")
                    ->orWhere('username', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', strtoupper($role));
        }

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        $perPage = $this->perPage($request, 50);
        $employees = $query->orderBy('employee_id', 'desc')->paginate($perPage);

        return $this->successResponse($employees, 'Employee directory retrieved');
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $employee = Employee::create([
            'employee_name' => $validated['employee_name'],
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'position' => $validated['position'] ?? 'STAFF',
            'username' => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'] ?? 'ACTIVE',
            'avatar_url' => $validated['avatar_url'] ?? null,
        ]);

        AuditLogService::log('CREATE', 'Employee', $employee->employee_id, null, [
            'username' => $employee->username,
            'role' => $employee->role,
        ]);

        return $this->createdResponse($employee, 'Employee registered successfully', '/api/v1/employees/'.$employee->employee_id);
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);

        return $this->successResponse($employee, 'Employee profile details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $old = ['username' => $employee->username, 'role' => $employee->role];

        $validated = $request->validate([
            'employee_name' => 'sometimes|required|string|max:150',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'sometimes|required|email|unique:employees,email,'.$id.',employee_id',
            'position' => 'nullable|string',
            'role' => 'sometimes|required|string|in:ADMIN,MANAGER,CASHIER,STAFF',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE',
            'avatar_url' => 'nullable|url|max:500',
            'password' => 'nullable|string|min:6',
        ]);

        if (! empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $employee->update($validated);

        AuditLogService::log('UPDATE', 'Employee', $id, $old, [
            'username' => $employee->username,
            'role' => $employee->role,
        ]);

        return $this->successResponse($employee, 'Employee updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $old = ['username' => $employee->username, 'role' => $employee->role];
        $employee->delete();

        AuditLogService::log('DELETE', 'Employee', $id, $old, null);

        return $this->successResponse(null, 'Employee soft deleted');
    }
}
