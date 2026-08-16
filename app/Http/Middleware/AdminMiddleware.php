<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request and enforce Admin role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message'           => 'សូមអភ័យទោស លោកអ្នកត្រូវចូលប្រព័ន្ធ (Login) ជាមុនសិន ទើបអាចដំណើរការបាន',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
                'status'            => '401',
            ], 401, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $isAdmin = ((bool) ($user->is_admin ?? false)) || 
                   (isset($user->role) && strtoupper($user->role) === 'ADMIN') ||
                   (isset($user->position) && str_contains(strtoupper($user->position), 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'message'           => 'ទាមទារសិទ្ធិជាអ្នកគ្រប់គ្រងជាន់ខ្ពស់ (Admin) ទើបអាចប្រើប្រាស់មុខងារនេះបាន',
                'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
                'status'            => '403',
            ], 403, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $next($request);
    }
}
