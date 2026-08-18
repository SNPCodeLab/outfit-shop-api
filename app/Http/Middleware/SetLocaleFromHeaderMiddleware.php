<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeaderMiddleware
{
    /**
     * Handle incoming request and set application locale from Accept-Language or X-Locale header.
     * Supported: en (English), km (Khmer), zh (Chinese).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'km', 'zh'];
        
        $header = $request->header('X-Locale') ?? $request->header('Accept-Language');
        
        if ($header) {
            $locale = strtolower(substr($header, 0, 2));
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
