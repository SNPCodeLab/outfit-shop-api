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
                'success' => false,
                'message' => 'Unauthenticated access.',
            ], 401);
        }

        $isAdmin = ($user->is_admin ?? false) || 
                   (isset($user->role) && strtoupper($user->role) === 'ADMIN') ||
                   (isset($user->position) && str_contains(strtoupper($user->position), 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Admin privileges required.',
            ], 403);
        }

        return $next($request);
    }
}
