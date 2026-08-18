<?php

namespace App\Http\Middleware;

use App\Http\Response\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request and enforce Admin role.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthenticated(
                'token_missing',
                'Authentication required. Please login to continue.'
            );
        }

        $isAdmin = ((bool) ($user->is_admin ?? false)) ||
                   (isset($user->role) && strtoupper($user->role) === 'ADMIN') ||
                   (isset($user->position) && str_contains(strtoupper($user->position), 'ADMIN'));

        if (! $isAdmin) {
            return ApiResponse::forbidden(
                'Admin privileges required to access this resource.'
            );
        }

        return $next($request);
    }
}
