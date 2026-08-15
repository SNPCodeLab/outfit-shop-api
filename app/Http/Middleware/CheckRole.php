<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request for Role-Based Access Control (RBAC).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated access.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if user is admin
        if ($user->is_admin || strtoupper($user->role ?? '') === 'ADMIN') {
            return $next($request);
        }

        // Check if employee role or Spatie role matches allowed roles list
        $userRole = strtoupper($user->role ?? '');
        $allowedRoles = array_map('strtoupper', $roles);

        $hasRoleMatch = in_array($userRole, $allowedRoles);

        if (!$hasRoleMatch && method_exists($user, 'hasAnyRole')) {
            $hasRoleMatch = $user->hasAnyRole($roles);
        }

        if (!$hasRoleMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Your role [' . ($user->role ?? 'NONE') . '] does not have permission to access this resource.',
                'error_code' => 'ERR_FORBIDDEN',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
