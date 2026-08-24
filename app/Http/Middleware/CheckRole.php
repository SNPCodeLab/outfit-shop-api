<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Response\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 *
 * Checks that the authenticated user has one of the required roles.
 * Works for both:
 *   - Employee model (column-based `role` field: ADMIN, MANAGER, CASHIER, STAFF)
 *   - User model (Spatie Permission HasRoles trait)
 *
 * Usage in routes:
 *   Route::middleware('role:MANAGER,ADMIN')
 *   Route::middleware('role:ADMIN')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthenticated(
                'token_missing',
                'Authentication required. Please login to continue.'
            );
        }

        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = strtoupper($trimmed);
                }
            }
        }
        $userRole = strtoupper($user->role ?? '');

        // 1. Admin is always allowed everywhere
        if ($userRole === 'ADMIN' || (property_exists($user, 'is_admin') && $user->is_admin)) {
            return $next($request);
        }

        // 2. Check column-based role (Employee model)
        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        // 3. Fallback: check Spatie HasRoles (User model)
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        return ApiResponse::forbidden(
            'You do not have sufficient permissions to access this resource.'
        );
    }
}
