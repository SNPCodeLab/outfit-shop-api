<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckRole::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'admin'      => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn () => null);

        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\LogApiRequests::class,
            \App\Http\Middleware\SetLocaleFromHeaderMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON error responses for all routes. Web routes that need HTML
        // errors should return null from the render callback (falls through to default).
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->render(function (Throwable $e, Request $request) {
            $isApi = $request->is('api/*') || $request->expectsJson();

            if (!$isApi) {
                return null;
            }

            // ----------------------------------------------------------------
            // 423 - Account locked (brute-force protection)
            // Must be evaluated before generic 401 handling.
            // ----------------------------------------------------------------
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            // ----------------------------------------------------------------
            // 401 - Unauthenticated
            // ----------------------------------------------------------------
            if ($e instanceof AuthenticationException) {
                return \App\Http\Response\ApiResponse::unauthenticated(
                    'token_missing',
                    'Invalid or expired authentication token.'
                );
            }

            // ----------------------------------------------------------------
            // 403 - Forbidden (Symfony AccessDeniedException from middleware)
            // ----------------------------------------------------------------
            if ($e instanceof AccessDeniedHttpException) {
                return \App\Http\Response\ApiResponse::forbidden(
                    'You do not have permission to perform this action.'
                );
            }

            // ----------------------------------------------------------------
            // 422 - Validation
            // ----------------------------------------------------------------
            if ($e instanceof ValidationException) {
                return \App\Http\Response\ApiResponse::validationError(
                    $e->errors(),
                    'The provided data failed validation.'
                );
            }

            // ----------------------------------------------------------------
            // 404 - Route not found
            // ----------------------------------------------------------------
            if ($e instanceof NotFoundHttpException) {
                return \App\Http\Response\ApiResponse::notFound(
                    'Resource',
                    null,
                    'The requested endpoint or resource was not found.'
                );
            }

            // ----------------------------------------------------------------
            // 404 - Eloquent findOrFail / firstOrFail
            // ----------------------------------------------------------------
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $model = class_basename($e->getModel());
                $ids   = implode(', ', $e->getIds());
                return \App\Http\Response\ApiResponse::notFound(
                    $model,
                    $ids,
                    "The requested {$model} was not found."
                );
            }

            // ----------------------------------------------------------------
            // 405 - Method not allowed
            // ----------------------------------------------------------------
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return \App\Http\Response\ApiResponse::methodNotAllowed(
                    $request->method(),
                    $request->path()
                );
            }

            // ----------------------------------------------------------------
            // 429 - Rate limit exceeded
            // ----------------------------------------------------------------
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
                || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                $headers    = $e->getHeaders();
                $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                $limit      = (int) ($headers['X-RateLimit-Limit'] ?? 60);
                $remaining  = (int) ($headers['X-RateLimit-Remaining'] ?? 0);
                return \App\Http\Response\ApiResponse::tooManyRequests($limit, $remaining, $retryAfter);
            }

            // ----------------------------------------------------------------
            // 500 - Fallback for all other unhandled exceptions
            // Debug detail is only included when APP_DEBUG=true (local/staging).
            // File paths and stack traces are never exposed in production.
            // ----------------------------------------------------------------
            $isDebug  = config('app.debug') || app()->isLocal();
            $debug    = $isDebug ? [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => array_slice(explode("\n", $e->getTraceAsString()), 0, 8),
            ] : null;

            $httpCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $httpCode = ($httpCode >= 400 && $httpCode < 600) ? $httpCode : 500;

            return \App\Http\Response\ApiResponse::error(
                'INTERNAL_SERVER_ERROR',
                $isDebug ? $e->getMessage() : 'An unexpected error occurred while processing the request.',
                null,
                $httpCode,
                true,
                5,
                $debug
            );
        });
    })->create();
