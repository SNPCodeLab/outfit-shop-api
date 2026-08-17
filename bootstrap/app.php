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
        // Force 100% JSON error responses across all routes (GitHub API style)
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->render(function (Throwable $e, Request $request) {
            $docUrl = rtrim(config('app.url', 'https://api.kesararamwithdigital.tech'), '/') . '/guide';
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success'           => false,
                    'message'           => 'សូមអភ័យទោស លោកអ្នកត្រូវចូលប្រព័ន្ធ (Login) ជាមុនសិន ទើបអាចដំណើរការបាន',
                    'documentation_url' => $docUrl,
                    'status'            => '401',
                ], 401, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'success'           => false,
                    'message'           => 'សូមអភ័យទោស គណនីរបស់លោកអ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ដើម្បីដំណើរការផ្នែកនេះទេ',
                    'documentation_url' => $docUrl,
                    'status'            => '403',
                ], 403, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success'           => false,
                    'message'           => 'ទិន្នន័យដែលបានបញ្ជូនមកមិនត្រឹមត្រូវតាមទម្រង់កំណត់ទេ សូមពិនិត្យមើលព័ត៌មានដែលបានបំពេញឡើងវិញ',
                    'errors'            => $e->errors(),
                    'documentation_url' => $docUrl,
                    'status'            => '422',
                ], 422, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success'           => false,
                    'message'           => 'រកមិនឃើញទិន្នន័យដែលលោកអ្នកកំពុងស្វែងរកទេ សូមពិនិត្យមើល URL ឡើងវិញម្តងទៀត',
                    'documentation_url' => $docUrl,
                    'status'            => '404',
                ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'success'           => false,
                    'message'           => 'លោកអ្នកបានផ្ញើសំណើញឹកញាប់ពេកហើយ សូមរង់ចាំបន្តិចសិន រួចសាកល្បងម្តងទៀត',
                    'documentation_url' => $docUrl,
                    'status'            => '429',
                ], 429, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            $data = [
                'success'           => false,
                'message'           => config('app.debug') || app()->isLocal() ? $e->getMessage() : 'ប្រព័ន្ធកំពុងជួបបញ្ហាបច្ចេកទេសបន្តិចបន្តួច ក្រុមការងារកំពុងដោះស្រាយ សូមអធ្យាស្រ័យ',
                'documentation_url' => $docUrl,
                'status'            => (string) ($status >= 400 && $status < 600 ? $status : 500),
            ];

            if (config('app.debug') || app()->isLocal()) {
                $data['details'] = $e->getMessage();
                $data['file']    = $e->getFile();
                $data['line']    = $e->getLine();
            }

            return response()->json($data, $status >= 400 && $status < 600 ? $status : 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        });
    })->create();

