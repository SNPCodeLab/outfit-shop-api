<?php

namespace App\Http\Middleware;

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
            return response()->json([
                'success'            => false,
                'message'            => 'Unauthenticated. Missing or invalid Authorization Bearer token.',
                'error_code'         => 'ERR_UNAUTHENTICATED',
                'hint'               => 'Please provide "Authorization: Bearer <TOKEN>" in your request headers. Log in at POST /api/v1/auth/login to get a token.',
                'documentation_url'  => 'https://github.com/SNPbuilds/csms-api',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map('strtoupper', $roles);
        $userRole     = strtoupper($user->role ?? '');

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

        return response()->json([
            'success'            => false,
            'message'            => 'Forbidden. You do not have permission to perform this action.',
            'error_code'         => 'ERR_FORBIDDEN',
            'your_role'          => $userRole ?: 'NONE',
            'required_role'      => implode(' or ', $allowedRoles),
            'documentation_url'  => 'https://github.com/SNPbuilds/csms-api',
        ], Response::HTTP_FORBIDDEN);
    }
}
