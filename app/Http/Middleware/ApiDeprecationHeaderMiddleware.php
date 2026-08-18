<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDeprecationHeaderMiddleware
{
    /**
     * Handle an incoming request and attach IETF standard Deprecation / Sunset headers.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string|null  $sunsetDate  e.g. "2027-12-31"
     */
    public function handle(Request $request, Closure $next, ?string $sunsetDate = null): Response
    {
        $response = $next($request);

        if ($sunsetDate) {
            $response->headers->set('Deprecation', 'true');
            $response->headers->set('Sunset', date(DATE_RFC1123, strtotime($sunsetDate)));
            $response->headers->set('Link', '<https://api.kesararamwithdigital.tech/api/v2/>; rel="successor-version"');
        }

        return $response;
    }
}
