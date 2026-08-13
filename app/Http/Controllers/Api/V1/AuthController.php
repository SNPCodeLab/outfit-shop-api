<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Employee;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Authenticate an employee and issue a Sanctum bearer token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password_hash)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid username or password.'],
            ]);
        }

        if ($employee->status !== 'ACTIVE') {
            return $this->errorResponse('Account is inactive. Please contact system administrator.', 403);
        }

        // Revoke previous tokens for clean login session (optional policy)
        $token = $employee->createToken('ssmis-api-token')->plainTextToken;

        AuditLogService::log(
            action: 'LOGIN',
            entity: 'Employee',
            entityId: $employee->employee_id,
            userId: $employee->employee_id
        );

        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'employee'     => [
                'employee_id'   => $employee->employee_id,
                'employee_name' => $employee->employee_name,
                'username'      => $employee->username,
                'email'         => $employee->email,
                'position'      => $employee->position,
                'role'          => $employee->role,
            ],
        ], 'Employee login successful');
    }

    /**
     * Get the current authenticated employee profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $employee = $request->user();

        return $this->successResponse([
            'employee_id'   => $employee->employee_id,
            'employee_name' => $employee->employee_name,
            'username'      => $employee->username,
            'email'         => $employee->email,
            'position'      => $employee->position,
            'role'          => $employee->role,
            'status'        => $employee->status,
        ], 'Authenticated user profile');
    }

    /**
     * Revoke the current access token (logout).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $employee = $request->user();

        if ($employee) {
            AuditLogService::log(
                action: 'LOGOUT',
                entity: 'Employee',
                entityId: $employee->employee_id,
                userId: $employee->employee_id
            );

            $request->user()->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Logout successful');
    }
}
