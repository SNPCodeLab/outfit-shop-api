<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
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

        $middleware->api(append: [
            \App\Http\Middleware\LogApiRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force 100% JSON error responses across all routes (GitHub API style)
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->render(function (Throwable $e, Request $request) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message'    => 'Unauthenticated. Valid Bearer token required.',
                    'error_code' => 'ERR_UNAUTHENTICATED',
                    'documentation_url' => 'https://github.com/SNPbuilds/csms-api'
                ], 401, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'message'    => $e->getMessage() ?: 'Forbidden: You do not have permission to perform this action.',
                    'error_code' => 'ERR_FORBIDDEN',
                    'documentation_url' => 'https://github.com/SNPbuilds/csms-api'
                ], 403, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message'    => 'Validation Failed',
                    'error_code' => 'ERR_VALIDATION',
                    'errors'     => $e->errors(),
                    'documentation_url' => 'https://github.com/SNPbuilds/csms-api'
                ], 422, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'message'    => 'Not Found',
                    'error_code' => 'ERR_NOT_FOUND',
                    'documentation_url' => 'https://github.com/SNPbuilds/csms-api'
                ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            return response()->json([
                'message'    => app()->isLocal() ? $e->getMessage() : 'Internal Server Error',
                'error_code' => 'ERR_INTERNAL_SERVER_ERROR',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api'
            ], $status >= 400 && $status < 600 ? $status : 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        });
    })->create();

if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || is_dir('/tmp')) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
