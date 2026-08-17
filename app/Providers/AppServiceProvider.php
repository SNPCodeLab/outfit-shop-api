<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;
use Illuminate\Foundation\FileBasedMaintenanceMode;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MaintenanceModeContract::class, function () {
            return new FileBasedMaintenanceMode();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Dynamic 4-Tier Role-Based Rate Limiting ───────────────────────────
        // Protects POS checkouts during peak sales while enforcing strict safety thresholds.
        \Illuminate\Support\Facades\RateLimiter::for('role-based', function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            if (!$user) {
                return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)
                    ->by($request->ip())
                    ->response(function () use ($request) {
                        return response()->json([
                            'success'    => false,
                            'message'    => 'Too many requests. Public rate limit exceeded (30 req/min).',
                            'error_code' => 'ERR_TOO_MANY_REQUESTS',
                            'request_id' => $request->header('X-Request-Id') ?? (string) \Illuminate\Support\Str::uuid(),
                        ], 429);
                    });
            }

            // Resolve Role
            $role = 'STAFF';
            if ($user instanceof \App\Models\Employee) {
                $role = strtoupper($user->role ?? 'STAFF');
            } elseif (property_exists($user, 'is_admin') && $user->is_admin) {
                $role = 'ADMIN';
            } elseif (method_exists($user, 'getRoleNames') && $user->getRoleNames()->first()) {
                $role = strtoupper($user->getRoleNames()->first());
            }

            $limits = [
                'ADMIN'   => 300,
                'MANAGER' => 200,
                'CASHIER' => 100,
                'STAFF'   => 50,
            ];

            $maxAttempts = $limits[$role] ?? 50;
            $userId = $user->id ?? $user->employee_id ?? $request->ip();

            return \Illuminate\Cache\RateLimiting\Limit::perMinute($maxAttempts)
                ->by((string) $userId)
                ->response(function () use ($role, $maxAttempts, $request) {
                    return response()->json([
                        'success'    => false,
                        'message'    => "Too many requests. Role [{$role}] limit exceeded ({$maxAttempts} req/min).",
                        'error_code' => 'ERR_TOO_MANY_REQUESTS',
                        'request_id' => $request->header('X-Request-Id') ?? (string) \Illuminate\Support\Str::uuid(),
                    ], 429);
                });
        });
    }
}
