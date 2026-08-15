<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Register a new user account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'nullable|string|in:admin,manager,cashier,viewer',
        ]);

        $roleName = strtolower($request->input('role', 'viewer'));

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $roleName === 'admin',
        ]);

        // Assign Spatie Role if model uses HasRoles
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($roleName);
        }

        $token = $user->createToken('ssmis-api-token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $roleName,
                'is_admin' => (bool) $user->is_admin,
            ],
        ], 'User registered successfully', 201);
    }

    /**
     * Authenticate user/employee credentials and issue a Sanctum token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required_without:email|string',
            'email'    => 'required_without:username|string',
            'password' => 'required|string',
        ]);

        $loginIdentifier = $request->input('username') ?? $request->input('email');

        // 1. Try Employee authentication
        $employee = Employee::where('username', $loginIdentifier)
            ->orWhere('email', $loginIdentifier)
            ->first();

        if ($employee && Hash::check($request->password, $employee->password_hash)) {
            if ($employee->status !== 'ACTIVE') {
                return $this->errorResponse('Account is inactive. Please contact system administrator.', 403);
            }

            $token = $employee->createToken('ssmis-api-token')->plainTextToken;

            if (class_exists(AuditLogService::class)) {
                AuditLogService::log(
                    action: 'LOGIN',
                    entity: 'Employee',
                    entityId: $employee->employee_id,
                    userId: $employee->employee_id
                );
            }

            return $this->successResponse([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'          => $employee->employee_id,
                    'name'        => $employee->employee_name,
                    'username'    => $employee->username,
                    'email'       => $employee->email,
                    'position'    => $employee->position,
                    'role'        => strtolower($employee->role ?? 'cashier'),
                ],
                'employee'     => [
                    'employee_id'   => $employee->employee_id,
                    'employee_name' => $employee->employee_name,
                    'username'      => $employee->username,
                    'email'         => $employee->email,
                    'position'      => $employee->position,
                    'role'          => strtolower($employee->role ?? 'cashier'),
                ],
            ], 'Employee login successful');
        }

        // 2. Try User authentication
        $user = User::where('email', $loginIdentifier)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('ssmis-api-token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'role'     => $user->is_admin ? 'admin' : 'viewer',
                    'is_admin' => (bool) $user->is_admin,
                ],
            ], 'User login successful');
        }

        throw ValidationException::withMessages([
            'username' => ['Invalid login credentials provided.'],
        ]);
    }

    /**
     * Get the current authenticated user/employee profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $account = $request->user();

        if ($account instanceof Employee) {
            return $this->successResponse([
                'id'       => $account->employee_id,
                'name'     => $account->employee_name,
                'username' => $account->username,
                'email'    => $account->email,
                'position' => $account->position,
                'role'     => strtolower($account->role ?? 'cashier'),
                'status'   => $account->status,
                'type'     => 'employee',
            ], 'Authenticated employee profile');
        }

        return $this->successResponse([
            'id'       => $account->id,
            'name'     => $account->name,
            'email'    => $account->email,
            'role'     => $account->is_admin ? 'admin' : 'viewer',
            'is_admin' => (bool) $account->is_admin,
            'type'     => 'user',
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
        $account = $request->user();

        if ($account) {
            if ($account instanceof Employee && class_exists(AuditLogService::class)) {
                AuditLogService::log(
                    action: 'LOGOUT',
                    entity: 'Employee',
                    entityId: $account->employee_id,
                    userId: $account->employee_id
                );
            }

            if (method_exists($account, 'currentAccessToken') && $account->currentAccessToken()) {
                $account->currentAccessToken()->delete();
            }
        }

        return $this->successResponse(null, 'Logout successful');
    }
}
