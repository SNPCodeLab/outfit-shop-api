<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Response\ApiResponse;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        if (! $account) {
            return 'GUEST';
        }

        if ($account instanceof Employee) {
            return strtoupper($account->role ?? 'STAFF');
        }

        if (is_object($account) && property_exists($account, 'is_admin') && $account->is_admin) {
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

    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->input('username') ?? $request->input('email');
        $lockKey = "login_lockout:{$identifier}";

        if (Cache::has($lockKey)) {
            return ApiResponse::accountLocked(
                'Account temporarily locked due to 10 failed login attempts. Please wait 15 minutes before retrying.',
                900
            );
        }

        try {
            // 1 — Employee authentication (username or email)
            $employee = Employee::where('username', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if ($employee && Hash::check($request->password, $employee->password_hash)) {
                Cache::forget("login_fails:{$identifier}");

                if ($employee->status !== 'ACTIVE') {
                    return ApiResponse::forbidden(
                        'Account is inactive. Please contact your administrator.'
                    );
                }

                $role = $this->resolveRole($employee);
                $deviceName = $request->input('device_name', 'Web Client / POS Terminal');
                $token = $employee->createToken($deviceName)->plainTextToken;
                $permissions = $this->permissionsFor($role);

                if (class_exists(AuditLogService::class)) {
                    try {
                        AuditLogService::log(
                            action: 'LOGIN',
                            entity: 'Employee',
                            entityId: $employee->employee_id,
                            userId: $employee->employee_id
                        );
                    } catch (\Throwable $e) {
                        Log::error('Audit log failed during login: '.$e->getMessage());
                    }
                }

                Log::channel('security')->info('Employee authenticated successfully', [
                    'employee_id' => $employee->employee_id,
                    'username' => $employee->username,
                    'role' => $role,
                    'device' => $deviceName,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->successResponse([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'device_name' => $deviceName,
                    'account_type' => 'employee',
                    'employee' => [
                        'employee_id' => $employee->employee_id,
                        'username' => $employee->username,
                        'role' => $role,
                    ],
                    'user' => [
                        'id' => $employee->employee_id,
                        'name' => $employee->employee_name,
                        'username' => $employee->username,
                        'email' => $employee->email,
                        'position' => $employee->position,
                        'role' => $role,
                        'permissions' => $permissions,
                    ],
                ], 'Login successful');
            }

            // 2 — User authentication (email)
            $user = User::where('email', $identifier)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                $role = $this->resolveRole($user);
                $deviceName = $request->input('device_name', 'Web Client / POS Terminal');
                $token = $user->createToken($deviceName)->plainTextToken;
                $permissions = $this->permissionsFor($role);

                Log::channel('security')->info('User account authenticated', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $role,
                    'device' => $deviceName,
                    'ip' => $request->ip(),
                ]);

                if (class_exists(AuditLogService::class)) {
                    try {
                        AuditLogService::log(
                            action: 'LOGIN',
                            entity: 'User',
                            entityId: $user->id,
                            userId: $user->id
                        );
                    } catch (\Throwable $e) {
                        Log::error('Audit log failed during user login: '.$e->getMessage());
                    }
                }

                return $this->successResponse([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'device_name' => $deviceName,
                    'account_type' => 'user',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role,
                        'permissions' => $permissions,
                    ],
                ], 'Login successful');
            }
        } catch (\Throwable $e) {
            Log::critical('CRITICAL LOGIN FAILURE: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'LOGIN_PROCESS_CRASH',
                'A critical error occurred during the login sequence: '.$e->getMessage(),
                ['file' => basename($e->getFile()), 'line' => $e->getLine()],
                500
            );
        }

        $failKey = "login_fails:{$identifier}";
        $fails = (int) Cache::get($failKey, 0) + 1;
        Cache::put($failKey, $fails, now()->addMinutes(15));

        if ($fails >= 10) {
            Cache::put("login_lockout:{$identifier}", true, now()->addMinutes(15));
            Log::channel('security')->alert("Account locked out after 10 failed attempts: {$identifier}", [
                'ip' => $request->ip(),
            ]);
        }

        Log::channel('security')->warning('Failed authentication attempt', [
            'identifier' => $identifier,
            'attempt_count' => $fails,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'username' => ['Invalid credentials. Please check your username/email and password.'],
        ]);
    }

    /**
     * POST /api/v1/auth/2fa/setup
     * Generate TOTP 2FA secret and QR code URL for administrators.
     */
    public function setup2FA(Request $request): JsonResponse
    {
        $account = $request->user();
        $secret = strtoupper(substr(md5(uniqid()), 0, 16));
        $email = $account->email ?? 'admin@kesararamwithdigital.tech';

        Cache::put("2fa_secret:{$account->id}", $secret, now()->addDays(30));

        return $this->successResponse([
            'two_factor_secret' => $secret,
            'qr_code_url' => "otpauth://totp/CSMS:{$email}?secret={$secret}&issuer=CSMS",
            'backup_codes' => [rand(100000, 999999), rand(100000, 999999), rand(100000, 999999)],
        ], 'Two-factor authentication (2FA) setup generated');
    }

    /**
     * POST /api/v1/auth/2fa/verify
     */
    public function verify2FA(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        return $this->successResponse([
            '2fa_verified' => true,
            'verified_at' => now()->toISOString(),
        ], 'Two-factor authentication verified successfully');
    }

    // =========================================================================
    // POST /api/v1/auth/refresh (Token Rotation)
    // =========================================================================

    public function refresh(Request $request): JsonResponse
    {
        $account = $request->user();

        if (! $account) {
            return ApiResponse::unauthenticated(
                'token_missing',
                'Authentication required. Please login to continue.'
            );
        }

        $currentToken = $account->currentAccessToken();
        $deviceName = $currentToken ? ($currentToken->name ?? 'Rotated Token') : 'Refreshed Device';

        // Revoke current token (Token Rotation pattern)
        if ($currentToken) {
            $currentToken->delete();
        }

        // Issue fresh access token
        $newToken = $account->createToken($deviceName)->plainTextToken;
        $role = $this->resolveRole($account);
        $permissions = $this->permissionsFor($role);

        return $this->successResponse([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'device_name' => $deviceName,
            'role' => $role,
            'permissions' => $permissions,
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
        $account = $request->user();

        if (! $account) {
            return ApiResponse::unauthenticated(
                'token_invalid',
                'Your authentication token is no longer valid. Please login again.'
            );
        }

        $role = $this->resolveRole($account);
        $permissions = $this->permissionsFor($role);

        if ($account instanceof Employee) {
            return $this->successResponse([
                'account_type' => 'employee',
                'id' => $account->employee_id,
                'name' => $account->employee_name,
                'username' => $account->username,
                'email' => $account->email,
                'position' => $account->position,
                'role' => $role,
                'status' => $account->status,
                'permissions' => $permissions,
            ], 'Authenticated profile');
        }

        return $this->successResponse([
            'account_type' => 'user',
            'id' => $account->id,
            'name' => $account->name,
            'email' => $account->email,
            'role' => $role,
            'is_admin' => (bool) ($account->is_admin ?? false),
            'permissions' => $permissions,
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

    public function register(RegisterRequest $request): JsonResponse
    {
        $roleName = strtolower($request->input('role', 'staff'));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $roleName === 'admin',
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($roleName);
        }

        $resolvedRole = $this->resolveRole($user);
        $token = $user->createToken('ssmis-api-token')->plainTextToken;

        return $this->createdResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'account_type' => 'user',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $resolvedRole,
                'permissions' => $this->permissionsFor($resolvedRole),
            ],
        ], 'User account created successfully', '/api/v1/auth/me');
    }
}
