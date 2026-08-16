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
                    'success'    => false,
                    'message'    => 'Access token is missing or invalid. Please sign in to continue.',
                    'error_code' => 'ERR_UNAUTHENTICATED',
                ], 401, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'You do not have permission to access this resource.',
                    'error_code' => 'ERR_FORBIDDEN',
                ], 403, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'Validation failed. Please check the submitted data.',
                    'error_code' => 'ERR_VALIDATION',
                    'errors'     => $e->errors(),
                ], 422, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'The requested resource was not found.',
                    'error_code' => 'ERR_NOT_FOUND',
                ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            $data = [
                'success'    => false,
                'message'    => config('app.debug') || app()->isLocal() ? $e->getMessage() : 'An unexpected error occurred. Please try again later.',
                'error_code' => 'ERR_INTERNAL_SERVER_ERROR',
            ];

            if (config('app.debug') || app()->isLocal()) {
                $data['details'] = $e->getMessage();
                $data['file']    = $e->getFile();
                $data['line']    = $e->getLine();
            }

            return response()->json($data, $status >= 400 && $status < 600 ? $status : 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        });
    })->create();

