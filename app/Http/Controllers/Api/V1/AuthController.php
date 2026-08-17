<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    // -------------------------------------------------------------------------
    // Permission map: what each role is allowed to do in the API.
    // Returned in login/me response so the frontend knows without asking.
    // -------------------------------------------------------------------------
    private const ROLE_PERMISSIONS = [
        'ADMIN' => [
            'catalog.read', 'catalog.write',
            'customers.read', 'customers.write',
            'sales.read', 'sales.checkout', 'sales.void',
            'suppliers.read', 'suppliers.write',
            'purchases.read', 'purchases.write',
            'stock.read', 'stock.adjust',
            'employees.read', 'employees.write',
            'dashboard.view', 'audit-logs.view',
            'users.create',
        ],
        'MANAGER' => [
            'catalog.read', 'catalog.write',
            'customers.read', 'customers.write',
            'sales.read', 'sales.checkout', 'sales.void',
            'suppliers.read', 'suppliers.write',
            'purchases.read', 'purchases.write',
            'stock.read', 'stock.adjust',
            'dashboard.view', 'audit-logs.view',
        ],
        'CASHIER' => [
            'catalog.read',
            'customers.read', 'customers.write',
            'sales.read', 'sales.checkout',
        ],
        'STAFF' => [
            'catalog.read',
            'customers.read',
            'sales.read',
        ],
    ];

    /**
     * Resolve the canonical UPPERCASE role string for any authenticatable model.
     */
    private function resolveRole(mixed $account): string
    {
        if ($account instanceof Employee) {
            return strtoupper($account->role ?? 'STAFF');
        }

        if (property_exists($account, 'is_admin') && $account->is_admin) {
            return 'ADMIN';
        }

        // Spatie role (User model)
        if (method_exists($account, 'getRoleNames')) {
            $spatieRole = $account->getRoleNames()->first();
            if ($spatieRole) {
                return strtoupper($spatieRole);
            }
        }

        return 'STAFF';
    }

    /**
     * Return the permission list for a given role string.
     */
    private function permissionsFor(string $role): array
    {
        return self::ROLE_PERMISSIONS[$role] ?? self::ROLE_PERMISSIONS['STAFF'];
    }

    // =========================================================================
    // POST /api/v1/auth/login
    // =========================================================================

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required_without:email|string',
            'email'    => 'required_without:username|string|email',
            'password' => 'required|string',
        ]);

        $identifier = $request->input('username') ?? $request->input('email');

        // 1 — Employee authentication (username or email)
        $employee = Employee::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if ($employee && Hash::check($request->password, $employee->password_hash)) {

            if ($employee->status !== 'ACTIVE') {
                return $this->errorResponse(
                    message:   'Account is inactive. Please contact your administrator.',
                    code:      403,
                    errorCode: 'ERR_ACCOUNT_INACTIVE'
                );
            }

            $role        = $this->resolveRole($employee);
            $deviceName  = $request->input('device_name', 'Web Client / POS Terminal');
            $token       = $employee->createToken($deviceName)->plainTextToken;
            $permissions = $this->permissionsFor($role);

            if (class_exists(AuditLogService::class)) {
                AuditLogService::log(
                    action: 'LOGIN',
                    entity: 'Employee',
                    entityId: $employee->employee_id,
                    userId: $employee->employee_id
                );
            }

            \Illuminate\Support\Facades\Log::channel('security')->info('Employee authenticated successfully', [
                'employee_id' => $employee->employee_id,
                'username'    => $employee->username,
                'role'        => $role,
                'device'      => $deviceName,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return $this->successResponse([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'device_name'  => $deviceName,
                'account_type' => 'employee',
                'employee'     => [
                    'employee_id' => $employee->employee_id,
                    'username'    => $employee->username,
                    'role'        => $role,
                ],
                'user' => [
                    'id'          => $employee->employee_id,
                    'name'        => $employee->employee_name,
                    'username'    => $employee->username,
                    'email'       => $employee->email,
                    'position'    => $employee->position,
                    'role'        => $role,
                    'permissions' => $permissions,
                ],
            ], 'Login successful');
        }

        // 2 — User authentication (email)
        $user = User::where('email', $identifier)->first();

        if ($user && Hash::check($request->password, $user->password)) {

            $role        = $this->resolveRole($user);
            $deviceName  = $request->input('device_name', 'Web Client / POS Terminal');
            $token       = $user->createToken($deviceName)->plainTextToken;
            $permissions = $this->permissionsFor($role);

            \Illuminate\Support\Facades\Log::channel('security')->info('User account authenticated', [
                'user_id'    => $user->id,
                'email'      => $user->email,
                'role'       => $role,
                'device'     => $deviceName,
                'ip'         => $request->ip(),
            ]);

            return $this->successResponse([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'device_name'  => $deviceName,
                'account_type' => 'user',
                'user' => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'role'        => $role,
                    'permissions' => $permissions,
                ],
            ], 'Login successful');
        }

        \Illuminate\Support\Facades\Log::channel('security')->warning('Failed authentication attempt', [
            'identifier' => $identifier,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'username' => ['Invalid credentials. Please check your username/email and password.'],
        ]);
    }

    // =========================================================================
    // POST /api/v1/auth/refresh (Token Rotation)
    // =========================================================================

    public function refresh(Request $request): JsonResponse
    {
        $account = $request->user();

        if (!$account) {
            return $this->errorResponse('Unauthenticated', 401, 'ERR_UNAUTHENTICATED');
        }

        $currentToken = $account->currentAccessToken();
        $deviceName   = $currentToken ? ($currentToken->name ?? 'Rotated Token') : 'Refreshed Device';

        // Revoke current token (Token Rotation pattern)
        if ($currentToken) {
            $currentToken->delete();
        }

        // Issue fresh access token
        $newToken    = $account->createToken($deviceName)->plainTextToken;
        $role        = $this->resolveRole($account);
        $permissions = $this->permissionsFor($role);

        return $this->successResponse([
            'access_token' => $newToken,
            'token_type'   => 'Bearer',
            'device_name'  => $deviceName,
            'role'         => $role,
            'permissions'  => $permissions,
        ], 'Token refreshed successfully with rotation');
    }

    // =========================================================================
    // POST /api/v1/auth/revoke-all (Security Kill Switch / Password Change)
    // =========================================================================

    public function revokeAll(Request $request): JsonResponse
    {
        $account = $request->user();

        if ($account && method_exists($account, 'tokens')) {
            $account->tokens()->delete();
        }

        return $this->successResponse(null, 'All active device sessions and tokens revoked successfully');
    }

    // =========================================================================
    // GET /api/v1/auth/me
    // =========================================================================

    public function me(Request $request): JsonResponse
    {
        $account     = $request->user();
        $role        = $this->resolveRole($account);
        $permissions = $this->permissionsFor($role);

        if ($account instanceof Employee) {
            return $this->successResponse([
                'account_type' => 'employee',
                'id'           => $account->employee_id,
                'name'         => $account->employee_name,
                'username'     => $account->username,
                'email'        => $account->email,
                'position'     => $account->position,
                'role'         => $role,
                'status'       => $account->status,
                'permissions'  => $permissions,
            ], 'Authenticated profile');
        }

        return $this->successResponse([
            'account_type' => 'user',
            'id'           => $account->id,
            'name'         => $account->name,
            'email'        => $account->email,
            'role'         => $role,
            'is_admin'     => (bool) ($account->is_admin ?? false),
            'permissions'  => $permissions,
        ], 'Authenticated profile');
    }

    // =========================================================================
    // POST /api/v1/auth/logout
    // =========================================================================

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

    // =========================================================================
    // POST /api/v1/auth/register  (ADMIN only — creates team accounts)
    // =========================================================================

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'nullable|string|in:admin,manager,cashier,staff,viewer',
        ]);

        $roleName = strtolower($request->input('role', 'staff'));

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $roleName === 'admin',
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($roleName);
        }

        $resolvedRole = $this->resolveRole($user);
        $token        = $user->createToken('ssmis-api-token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'account_type' => 'user',
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $resolvedRole,
                'permissions' => $this->permissionsFor($resolvedRole),
            ],
        ], 'User account created successfully', 201);
    }
}
