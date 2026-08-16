<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request and enforce Admin role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success'           => false,
                'message'           => 'Unauthenticated. Missing or invalid Authorization Bearer token.',
                'error_code'        => 'ERR_UNAUTHENTICATED',
                'hint'              => 'Please provide "Authorization: Bearer <TOKEN>" in your request headers. Log in at POST /api/v1/auth/login to get a token.',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
            ], 401);
        }

        $isAdmin = ((bool) ($user->is_admin ?? false)) || 
                   (isset($user->role) && strtoupper($user->role) === 'ADMIN') ||
                   (isset($user->position) && str_contains(strtoupper($user->position), 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success'           => false,
                'message'           => 'Forbidden. Admin privileges required.',
                'error_code'        => 'ERR_FORBIDDEN',
                'hint'              => 'Your account requires the ADMIN role to perform this action.',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
            ], 403);
        }

        return $next($request);
    }
}
