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
use App\Services\CloudinaryService;
use App\Support\Totp;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function __construct(protected CloudinaryService $cloudinary) {}

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

    /**
     * Sanctum token abilities derived from the role permission map, giving
     * each token the least privilege its role needs. Legacy tokens issued
     * with ['*'] keep working (Sanctum treats '*' as pass-all ability).
     */
    private function tokenAbilities(string $role): array
    {
        return $this->permissionsFor($role);
    }

    // =========================================================================
    // POST /api/v1/auth/login
    // =========================================================================

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $identifier = $request->input('username') ?? $request->input('email');
            $lockKey = "login_lockout:{$identifier}";

            if (Cache::has($lockKey)) {
                return ApiResponse::accountLocked(
                    'Account temporarily locked due to 10 failed login attempts. Please wait 15 minutes before retrying.',
                    900
                );
            }

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
                $token = $employee->createToken($deviceName, $this->tokenAbilities($role))->plainTextToken;
                $permissions = $this->permissionsFor($role);

                // Login telemetry for staff audit trails
                $employee->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ])->save();

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
                $token = $user->createToken($deviceName, $this->tokenAbilities($role))->plainTextToken;
                $permissions = $this->permissionsFor($role);

                // Login telemetry for system accounts
                $user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ])->save();

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

            // Failed authentication handling
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

            // 401 with a generic message - no user enumeration and no hint
            // about which credential field was wrong (GitHub/Stripe convention).
            return ApiResponse::unauthenticated(
                'invalid_credentials',
                'Invalid credentials. Please check your username/email and password.'
            );

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::critical('CRITICAL LOGIN FAILURE: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $isDebug = (bool) config('app.debug');

            return ApiResponse::error(
                'LOGIN_PROCESS_CRASH',
                $isDebug
                    ? 'A critical error occurred during the login sequence: '.$e->getMessage()
                    : 'A critical error occurred during the login sequence. Please try again later.',
                $isDebug ? ['file' => basename($e->getFile()), 'line' => $e->getLine()] : null,
                500
            );
        }
    }

    /**
     * POST /api/v1/auth/2fa/setup
     *
     * Generates a cryptographically secure RFC 6238 TOTP secret, persists it
     * encrypted on the account, and returns an otpauth:// URL compatible with
     * Google Authenticator / 1Password. The secret is stored at rest encrypted
     * with the application key - never in plain cache.
     */
    public function setup2FA(Request $request): JsonResponse
    {
        $account = $request->user();

        if (! $account) {
            return $this->unauthorizedResponse('token_missing', 'Authentication required to configure 2FA.');
        }

        $secret = Totp::generateSecret();
        $email = $account->email ?? 'account@kesararamwithdigital.tech';

        $account->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_verified_at' => null,
        ])->save();

        Log::channel('security')->info('2FA secret generated for account', [
            'account_id' => $account->employee_id ?? $account->id,
            'ip' => $request->ip(),
        ]);

        return $this->successResponse([
            'two_factor_secret' => $secret,
            'otpauth_url' => 'otpauth://totp/OutfitShop:'.rawurlencode($email).'?secret='.$secret.'&issuer=OutfitShop&algorithm=SHA1&digits=6&period=30',
            'instructions' => 'Scan the otpauth URL with your authenticator app, then verify with POST /auth/2fa/verify.',
        ], 'Two-factor authentication (2FA) setup generated');
    }

    /**
     * POST /api/v1/auth/2fa/verify
     *
     * Verifies the submitted 6-digit code against the stored TOTP secret
     * (RFC 6238, +/-1 step clock-skew tolerance, constant-time comparison).
     */
    public function verify2FA(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $account = $request->user();

        if (! $account) {
            return $this->unauthorizedResponse('token_missing', 'Authentication required to verify 2FA.');
        }

        $encryptedSecret = $account->two_factor_secret ?? null;

        if (empty($encryptedSecret)) {
            return $this->errorResponse(
                'Two-factor authentication is not configured for this account. Call POST /auth/2fa/setup first.',
                409,
                'TWO_FACTOR_NOT_CONFIGURED'
            );
        }

        $secret = decrypt($encryptedSecret);
        $code = preg_replace('/\s+/', '', $request->input('code')) ?? '';

        if (Totp::verify($secret, $code)) {
            $account->forceFill(['two_factor_verified_at' => now()])->save();

            Log::channel('security')->info('2FA verification succeeded', [
                'account_id' => $account->employee_id ?? $account->id,
                'ip' => $request->ip(),
            ]);

            return $this->successResponse([
                '2fa_verified' => true,
                'verified_at' => now()->toISOString(),
            ], 'Two-factor authentication verified successfully');
        }

        Log::channel('security')->warning('2FA verification failed (invalid code)', [
            'account_id' => $account->employee_id ?? $account->id,
            'ip' => $request->ip(),
        ]);

        return $this->errorResponse(
            'Invalid two-factor authentication code.',
            401,
            'INVALID_TWO_FACTOR_CODE'
        );
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

        // Issue fresh access token with the same least-privilege abilities a
        // login would grant, so rotation never widens the token to ['*']
        $role = $this->resolveRole($account);
        $newToken = $account->createToken($deviceName, $this->tokenAbilities($role))->plainTextToken;
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
                'phone' => $account->phone,
                'avatar_url' => $account->avatar_url,
                'joined_at' => optional($account->joined_at)->toDateString(),
                'last_login_at' => optional($account->last_login_at)->toISOString(),
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
            'username' => $account->username,
            'email' => $account->email,
            'phone' => $account->phone,
            'avatar_url' => $account->avatar_url,
            'joined_at' => optional($account->joined_at)->toDateString(),
            'last_login_at' => optional($account->last_login_at)->toISOString(),
            'role' => $role,
            'is_admin' => (bool) ($account->is_admin ?? false),
            'status' => $account->status,
            'permissions' => $permissions,
        ], 'Authenticated profile');
    }

    // =========================================================================
    // POST /api/v1/auth/avatar (Self-service profile picture, any role)
    // =========================================================================

    /**
     * Set the profile picture for the authenticated account (employee or
     * user). Accepts a multipart image upload (stored on Cloudinary under
     * khmeriel/avatars with a stable public id, so re-uploads overwrite the
     * previous asset instead of accumulating) or a hosted avatar_url string.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required_without:avatar_url|file|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'avatar_url' => 'required_without:image|nullable|url|max:500',
        ]);

        if (! $request->hasFile('image') && ! $request->filled('avatar_url')) {
            return ApiResponse::validationError(
                ['image' => ['An image file or an avatar_url must be provided.']],
                'No avatar source provided.'
            );
        }

        $account = $request->user();
        $previousAvatar = $account->avatar_url;

        try {
            if ($request->hasFile('image')) {
                $publicId = ($account instanceof Employee ? 'employee-'.$account->employee_id : 'user-'.$account->id).'-avatar';
                $upload = $this->cloudinary->upload($request->file('image'), 'khmeriel/avatars', $publicId);
                $avatarUrl = (string) $upload['secure_url'];
            } else {
                $avatarUrl = (string) $request->input('avatar_url');
            }
        } catch (Exception $e) {
            Log::error('Avatar upload failed', ['error' => $e->getMessage()]);

            return ApiResponse::error(
                'AVATAR_UPLOAD_FAILED',
                'The profile picture could not be stored. Please retry.',
                null,
                500
            );
        }

        $account->fill(['avatar_url' => $avatarUrl])->save();

        AuditLogService::log(
            action: 'UPDATE_AVATAR',
            entity: $account instanceof Employee ? 'Employee' : 'User',
            entityId: $account instanceof Employee ? $account->employee_id : $account->id,
            oldValues: ['avatar_url' => $previousAvatar],
            newValues: ['avatar_url' => $avatarUrl],
            userId: $account instanceof Employee ? $account->employee_id : $account->id
        );

        return $this->successResponse([
            'avatar_url' => $avatarUrl,
        ], 'Profile picture updated successfully');
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
            'username' => $request->email,
            'joined_at' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Privilege flag set only via forceFill (is_admin is not fillable)
        $user->forceFill(['is_admin' => $roleName === 'admin'])->save();

        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole($roleName);
            } catch (\Throwable $roleError) {
                // Spatie role table not seeded for this role name - column
                // based role resolution still authenticates the account;
                // fail soft instead of 500ing the whole registration.
                Log::warning('Spatie role assignment skipped during registration', [
                    'user_id' => $user->id,
                    'role' => $roleName,
                    'reason' => $roleError->getMessage(),
                ]);
            }
        }

        $resolvedRole = $this->resolveRole($user);

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log(
                action: 'CREATE',
                entity: 'User',
                entityId: $user->id,
                newValues: ['email' => $user->email, 'role' => $resolvedRole],
                userId: $request->user()->employee_id ?? $request->user()->id ?? null
            );
        }

        Log::channel('admin')->info('Admin created a user account', [
            'new_user_id' => $user->id,
            'new_user_email' => $user->email,
            'role' => $resolvedRole,
            'created_by' => $request->user()->email ?? 'unknown',
        ]);

        // No token is issued at creation: the new account (or an attacker who
        // knows its credentials) must authenticate through POST /auth/login.
        return $this->createdResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $resolvedRole,
                'permissions' => $this->permissionsFor($resolvedRole),
            ],
            'next_step' => 'Authenticate via POST /api/v1/auth/login to obtain a token.',
        ], 'User account created successfully', '/api/v1/auth/me');
    }

    // =========================================================================
    // POST /api/v1/auth/forgot-password (M10)
    // =========================================================================

    /**
     * Issue a single-use, 30-minute password-reset token for the account
     * owning the given email. The token is written to the security log for
     * retrieval by operations (this deployment has no outbound mailer).
     * Response is always generic - never reveals whether the email exists.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));

        $employee = Employee::where('email', $email)->first();
        $user = User::where('email', $email)->first();

        $devToken = null;

        if ($employee || $user) {
            $token = Str::random(64);
            Cache::put(
                "password_reset:{$email}",
                hash('sha256', $token),
                now()->addMinutes(30)
            );

            Log::channel('security')->info('Password reset token issued', [
                'email' => $email,
                'ip' => $request->ip(),
                'expires_at' => now()->addMinutes(30)->toISOString(),
            ]);

            // Mail is not configured on this deployment; operations reads the
            // token from the security channel. In debug/dev it is returned
            // inline for immediate testing convenience only.
            if (config('app.debug')) {
                $devToken = $token;
            } else {
                Log::channel('security')->notice("Password reset token for {$email}: {$token}");
            }
        }

        return $this->successResponse(
            $devToken ? ['reset_token' => $devToken] : null,
            'If the email belongs to an account, a reset token has been issued and written to the security log.'
        );
    }

    // =========================================================================
    // POST /api/v1/auth/reset-password (M10)
    // =========================================================================

    /**
     * Complete a password reset: verify the single-use token, set the new
     * password on every account bound to the email (Employee and User), and
     * revoke all active tokens for those accounts (credential compromise
     * kill switch).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:64',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower(trim($request->input('email')));
        $cacheKey = "password_reset:{$email}";
        $storedHash = Cache::get($cacheKey);

        if (! $storedHash || ! hash_equals($storedHash, hash('sha256', $request->input('token')))) {
            return $this->errorResponse(
                'Invalid or expired password reset token.',
                401,
                'INVALID_RESET_TOKEN'
            );
        }

        $employee = Employee::where('email', $email)->first();
        $user = User::where('email', $email)->first();

        if (! $employee && ! $user) {
            Cache::forget($cacheKey);

            return $this->errorResponse(
                'Invalid or expired password reset token.',
                401,
                'INVALID_RESET_TOKEN'
            );
        }

        if ($employee) {
            $employee->forceFill(['password_hash' => Hash::make($request->input('password'))])->save();
            if (method_exists($employee, 'tokens')) {
                $employee->tokens()->delete();
            }
        }

        if ($user) {
            $user->forceFill(['password' => Hash::make($request->input('password'))])->save();
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
        }

        Cache::forget($cacheKey);

        Log::channel('security')->info('Password reset completed; all sessions revoked', [
            'email' => $email,
            'ip' => $request->ip(),
        ]);

        return $this->successResponse(
            null,
            'Password reset successfully. All active sessions were revoked - please login again.'
        );
    }

    // =========================================================================
    // POST /api/v1/auth/admin-reset-password (ADMIN)
    // =========================================================================

    /**
     * Admin-driven credential reset for internal MIS operations (no mailer
     * required). Revokes all target-account tokens after the change.
     */
    public function adminResetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:8',
        ]);

        $email = strtolower(trim($request->input('email')));
        $employee = Employee::where('email', $email)->first();
        $user = User::where('email', $email)->first();

        if (! $employee && ! $user) {
            return $this->notFoundResponse('Account', $email, 'No account exists with that email address.');
        }

        if ($employee) {
            $employee->forceFill(['password_hash' => Hash::make($request->input('new_password'))])->save();
            if (method_exists($employee, 'tokens')) {
                $employee->tokens()->delete();
            }
        }

        if ($user) {
            $user->forceFill(['password' => Hash::make($request->input('new_password'))])->save();
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
        }

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log('PASSWORD_RESET', 'Account', $email);
        }

        Log::channel('admin')->alert('Admin-forced password reset executed', [
            'target_email' => $email,
            'performed_by' => $request->user()?->email ?? 'admin',
            'ip' => $request->ip(),
        ]);

        return $this->successResponse(null, 'Password reset and all sessions revoked for the target account.');
    }
}
