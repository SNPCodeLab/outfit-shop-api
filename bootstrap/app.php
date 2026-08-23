<?php

declare(strict_types=1);

use App\Http\Middleware\AdminIpWhitelistMiddleware;
use App\Http\Middleware\ApiDeprecationHeaderMiddleware;
use App\Http\Middleware\CambodiaOnlyMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\LogApiRequests;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetLocaleFromHeaderMiddleware;
use App\Http\Response\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => PermissionMiddleware::class,
            'deprecated' => ApiDeprecationHeaderMiddleware::class,
            'admin.ip' => AdminIpWhitelistMiddleware::class,
            'cambodia.only' => CambodiaOnlyMiddleware::class,
            'ability' => CheckForAnyAbility::class,
            'abilities' => CheckAbilities::class,
        ]);

        $middleware->redirectGuestsTo(fn () => null);

        $middleware->api(append: [
            SecurityHeadersMiddleware::class,
            LogApiRequests::class,
            SetLocaleFromHeaderMiddleware::class,
        ]);

        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON error responses for all routes. Web routes that need HTML
        // errors should return null from the render callback (falls through to default).
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->render(function (Throwable $e, Request $request) {
            $isApi = $request->is('api/*') || $request->expectsJson();

            if (! $isApi) {
                return null;
            }

            // ----------------------------------------------------------------
            // 423 - Account locked (brute-force protection)
            // Must be evaluated before generic 401 handling.
            // ----------------------------------------------------------------
            if ($e instanceof HttpResponseException) {
                return null;
            }

            // ----------------------------------------------------------------
            // 401 - Unauthenticated
            // ----------------------------------------------------------------
            if ($e instanceof AuthenticationException) {
                return ApiResponse::unauthenticated(
                    'token_missing',
                    'Invalid or expired authentication token.'
                );
            }

            // ----------------------------------------------------------------
            // 403 - Forbidden (Symfony AccessDeniedException from middleware)
            // ----------------------------------------------------------------
            if ($e instanceof AccessDeniedHttpException) {
                return ApiResponse::forbidden(
                    'You do not have permission to perform this action.'
                );
            }

            // ----------------------------------------------------------------
            // 422 - Validation
            // ----------------------------------------------------------------
            if ($e instanceof ValidationException) {
                return ApiResponse::validationError(
                    $e->errors(),
                    'The provided data failed validation.'
                );
            }

            // ----------------------------------------------------------------
            // 404 - Route not found
            // ----------------------------------------------------------------
            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::notFound(
                    'Resource',
                    null,
                    'The requested endpoint or resource was not found.'
                );
            }

            // ----------------------------------------------------------------
            // 404 - Eloquent findOrFail / firstOrFail
            // ----------------------------------------------------------------
            if ($e instanceof ModelNotFoundException) {
                $model = class_basename($e->getModel());
                $ids = implode(', ', $e->getIds());

                return ApiResponse::notFound(
                    $model,
                    $ids,
                    "The requested {$model} was not found."
                );
            }

            // ----------------------------------------------------------------
            // 405 - Method not allowed
            // ----------------------------------------------------------------
            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponse::methodNotAllowed(
                    $request->method(),
                    $request->path()
                );
            }

            // ----------------------------------------------------------------
            // 429 - Rate limit exceeded
            // ----------------------------------------------------------------
            if ($e instanceof TooManyRequestsHttpException
                || $e instanceof ThrottleRequestsException) {
                $headers = $e->getHeaders();
                $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                $limit = (int) ($headers['X-RateLimit-Limit'] ?? 60);
                $remaining = (int) ($headers['X-RateLimit-Remaining'] ?? 0);

                return ApiResponse::tooManyRequests($limit, $remaining, $retryAfter);
            }

            // ----------------------------------------------------------------
            // 500 - Fallback for all other unhandled exceptions
            // Debug detail is only included when APP_DEBUG=true (local/staging).
            // File paths and stack traces are never exposed in production.
            // ----------------------------------------------------------------
            $isDebug = config('app.debug') || app()->isLocal();
            $debug = $isDebug ? [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 8),
            ] : null;

            $httpCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $httpCode = ($httpCode >= 400 && $httpCode < 600) ? $httpCode : 500;

            return ApiResponse::error(
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
