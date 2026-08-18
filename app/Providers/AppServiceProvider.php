<?php

namespace App\Providers;

use App\Http\Response\ApiResponse;
use App\Models\Employee;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;
use Illuminate\Foundation\FileBasedMaintenanceMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MaintenanceModeContract::class, function () {
            return new FileBasedMaintenanceMode;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Dynamic 4-Tier Role-Based Rate Limiting ───────────────────────────
        // Protects POS checkouts during peak sales while enforcing strict safety thresholds.
        RateLimiter::for('role-based', function (Request $request) {
            $user = $request->user();

            if (! $user) {
                return Limit::perMinute(30)
                    ->by($request->ip())
                    ->response(function () {
                        return ApiResponse::tooManyRequests(30, 0, 60, 'PUBLIC');
                    });
            }

            // Resolve Role
            $role = 'STAFF';
            if ($user instanceof Employee) {
                $role = strtoupper($user->role ?? 'STAFF');
            } elseif (property_exists($user, 'is_admin') && $user->is_admin) {
                $role = 'ADMIN';
            } elseif (method_exists($user, 'getRoleNames') && $user->getRoleNames()->first()) {
                $role = strtoupper($user->getRoleNames()->first());
            }

            $limits = [
                'ADMIN' => 300,
                'MANAGER' => 200,
                'CASHIER' => 100,
                'STAFF' => 50,
            ];

            $maxAttempts = $limits[$role] ?? 50;
            $userId = $user->id ?? $user->employee_id ?? $request->ip();

            return Limit::perMinute($maxAttempts)
                ->by((string) $userId)
                ->response(function () use ($role, $maxAttempts) {
                    return ApiResponse::tooManyRequests(
                        $maxAttempts, 0, 60, $role
                    );
                });
        });
    }
}
