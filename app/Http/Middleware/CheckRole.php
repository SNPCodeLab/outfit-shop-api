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
                'message'           => 'សូមអភ័យទោស! លោកអ្នកត្រូវចូលប្រព័ន្ធ (Login) ជាមុនសិន ទើបអាចដំណើរការបាន 🔐✨',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
                'status'            => '401',
            ], Response::HTTP_UNAUTHORIZED, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
            'message'           => 'សូមអភ័យទោស! គណនីរបស់លោកអ្នកមិនទាន់មានសិទ្ធិគ្រប់គ្រាន់ដើម្បីបើកមើលផ្នែកនេះទេ 🚫',
            'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
            'status'            => '403',
        ], Response::HTTP_FORBIDDEN, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
