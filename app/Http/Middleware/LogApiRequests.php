<?php

declare(strict_types=1);

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
     * @param  Closure(Request): (Response)  $next
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

        // Extract request_id from the JSON response body so it links back to what was returned to the client
        $requestId = null;
        if (is_string($content) && str_contains($content, 'request_id')) {
            $decoded = json_decode($content, true);
            $requestId = $decoded['request_id'] ?? null;
        }
        // Fallback: use X-Request-Id header sent by the client (frontend interceptor)
        if (! $requestId) {
            $headerValue = $request->header('X-Request-Id');
            $requestId = is_string($headerValue) ? $headerValue : null;
        }

        try {
            ApiLog::create([
                'request_id' => $requestId,
                'user_id' => $userId ? (string) $userId : null,
                'token_name' => $tokenName,
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
                'ip' => $request->ip(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'response_size' => $responseSize,
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures to not disrupt primary API execution
        }

        return $response;
    }
}
