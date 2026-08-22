<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Response\ApiResponse;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;
use Illuminate\Foundation\FileBasedMaintenanceMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
        // ── Deterministic names for every route (error tracing, route()) ────
        // Routes without an explicit name receive a computed one so the whole
        // surface is addressable; explicit names (login, products.show, ...)
        // always win.
        $this->app->booted(function (): void {
            $taken = [];
            foreach (Route::getRoutes() as $route) {
                if ($route->getName()) {
                    $taken[$route->getName()] = true;

                    continue;
                }

                $method = strtolower(collect($route->methods())
                    ->first(fn ($m) => ! in_array($m, ['HEAD', 'OPTIONS'])) ?? 'any');
                $slug = trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($route->uri())), '-');
                $name = "api.{$method}.".str_replace(['api-', 'v1-'], '', $slug);

                $suffix = 2;
                $base = $name;
                while (isset($taken[$name])) {
                    $name = $base.'-'.$suffix++;
                }
                $taken[$name] = true;
                $route->name($name);
            }
        });

        // ── Event-driven cache invalidation (any write path flushes caches) ──
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        Category::observe(CategoryObserver::class);

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
