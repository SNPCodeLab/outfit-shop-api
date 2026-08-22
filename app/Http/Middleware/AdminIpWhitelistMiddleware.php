<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Response\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelistMiddleware
{
    /**
     * Enforce IP allow-listing (single IPs or CIDR ranges) on high-privilege
     * administrative endpoints. Configured via ADMIN_IP_WHITELIST (comma
     * separated, e.g. "203.0.113.7,10.0.0.0/8"). When unset the feature is
     * disabled and all IPs pass - once set, enforcement is strict with no
     * built-in bypasses.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config('auth.admin_ip_whitelist', env('ADMIN_IP_WHITELIST'));

        if (empty($whitelist)) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', (string) $whitelist));
        $clientIp = (string) $request->ip();

        foreach ($allowed as $entry) {
            if ($entry === '') {
                continue;
            }

            $matches = str_contains($entry, '/')
                ? IpUtils::checkIp($clientIp, $entry)
                : hash_equals($entry, $clientIp);

            if ($matches) {
                return $next($request);
            }
        }

        return ApiResponse::forbidden(
            'Access restricted: Your IP address is not authorized for administrative operations.'
        );
    }
}
