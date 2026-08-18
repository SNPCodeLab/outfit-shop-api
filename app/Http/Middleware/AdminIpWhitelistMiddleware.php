<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelistMiddleware
{
    /**
     * Handle an incoming request and enforce IP whitelisting for high-privilege administrative endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config('auth.admin_ip_whitelist', env('ADMIN_IP_WHITELIST'));

        // If no whitelist is configured, allow all
        if (empty($whitelist)) {
            return $next($request);
        }

        $allowedIps = array_map('trim', explode(',', $whitelist));
        $clientIp = $request->ip();

        if (!in_array($clientIp, $allowedIps) && !in_array('127.0.0.1', $allowedIps) && !app()->isLocal()) {
            return \App\Http\Response\ApiResponse::forbidden(
                'Access restricted: Your IP address is not authorized for administrative operations.'
            );
        }

        return $next($request);
    }
}
