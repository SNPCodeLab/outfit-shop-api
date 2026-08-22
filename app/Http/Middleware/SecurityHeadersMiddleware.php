<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and enforce enterprise security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Correlation: echo the inbound X-Request-Id (max 64 chars) or mint
        // a UUID. Setting it on the request first keeps the JSON envelope
        // (ApiResponse echoes the same header) and the response header in
        // sync, so non-JSON downloads are also traceable.
        $requestId = $request->header('X-Request-Id');
        if (! $requestId || strlen($requestId) > 64) {
            $requestId = (string) Str::uuid();
        }
        $request->headers->set('X-Request-Id', $requestId);

        $response = $next($request);

        // Distributed tracing header (GitHub: X-GitHub-Request-Id convention)
        $response->headers->set('X-Request-Id', $requestId);

        // Security headers against clickjacking, MIME sniffing, XSS, and downgrade attacks.
        // CSP tightened for a JSON API: no unsafe-eval; inline styles allowed
        // only for the HTML guide/receipt renders.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; img-src 'self' https: data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'self'; base-uri 'self'"
        );

        return $response;
    }
}
