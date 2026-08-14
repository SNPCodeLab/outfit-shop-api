<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request and log API traffic stats.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        
        $user = $request->user();
        $userId = $user ? ($user->id ?? $user->employee_id ?? null) : null;
        $tokenName = null;

        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $tokenName = $user->currentAccessToken()->name ?? 'sanctum_token';
        }

        $content = $response->getContent();
        $responseSize = is_string($content) ? strlen($content) : 0;

        try {
            ApiLog::create([
                'user_id'       => $userId ? (string) $userId : null,
                'token_name'    => $tokenName,
                'method'        => $request->method(),
                'path'          => '/' . ltrim($request->path(), '/'),
                'ip'            => $request->ip(),
                'status'        => $response->getStatusCode(),
                'duration_ms'   => $durationMs,
                'response_size' => $responseSize,
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures to not disrupt primary API execution
        }

        return $response;
    }
}
