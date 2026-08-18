<?php

namespace App\Http\Response;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * ApiResponse
 *
 * Central factory for all JSON response envelopes across the SS-MIS API.
 *
 * Standard conforms to:
 *   - JSend specification
 *   - GitHub REST API conventions
 *   - Google JSON Style Guide
 *   - RFC 7807 Problem Details for HTTP APIs
 *
 * Every response includes:
 *   success          bool    - true on 2xx, false on 4xx/5xx
 *   status_code      int     - mirrors the HTTP status code
 *   request_id       string  - UUID v4, echoes X-Request-Id header when provided
 *   timestamp        string  - ISO 8601 UTC server time
 *   message          string  - human-readable summary
 *   data             mixed   - resource payload (null on deletions/errors)
 *   meta             object  - api_version, processing_time_ms, pagination, cache
 *   links            object  - self, next, previous (on collections)
 *   error            object  - structured error detail (4xx/5xx only)
 */
class ApiResponse
{
    // =========================================================================
    // SUCCESS RESPONSES
    // =========================================================================

    /**
     * Build a standard success response envelope.
     *
     * Automatically detects LengthAwarePaginator and CursorPaginator instances
     * and extracts data + meta.pagination from them without manual wrapping.
     *
     * @param  mixed  $data  Resource, collection, array, paginator, or null
     * @param  string  $message  Human-readable summary
     * @param  array  $meta  Additional meta fields merged into the meta block
     * @param  array  $links  Override the links block
     * @param  int  $statusCode  HTTP status code (default 200)
     * @param  array  $filters  Applied filters surfaced in meta.filters_applied
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        array $meta = [],
        array $links = [],
        int $statusCode = 200,
        array $filters = []
    ): JsonResponse {
        $requestId = self::resolveRequestId();
        $processingTime = self::processingTimeMs();

        $baseMeta = [
            'system' => config('api.system_name', 'OutfitShop Ecommerce Clothing API'),
            'api_version' => config('api.version', '1.0.0'),
            'processing_time_ms' => $processingTime,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $paginationMeta = self::buildPaginationMeta($data, $filters);
            $resolvedLinks = self::buildPaginationLinks($data);
            $resolvedData = $data->items();
            $resolvedMeta = array_merge($baseMeta, $paginationMeta, $meta);
        } elseif ($data instanceof CursorPaginator) {
            $paginationMeta = self::buildCursorMeta($data);
            $resolvedLinks = [
                'self' => $data->path(),
                'next' => $data->nextPageUrl(),
                'previous' => $data->previousPageUrl(),
            ];
            $resolvedData = $data->items();
            $resolvedMeta = array_merge($baseMeta, $paginationMeta, $meta);
        } else {
            $resolvedData = $data ?? [];
            $resolvedMeta = array_merge($baseMeta, $meta);
            $resolvedLinks = $links;
        }

        $envelope = [
            'success' => true,
            'status_code' => $statusCode,
            'request_id' => $requestId,
            'timestamp' => now()->toISOString(),
            'message' => $message,
            'data' => $resolvedData,
            'meta' => $resolvedMeta,
        ];

        if (! empty($resolvedLinks)) {
            $envelope['links'] = $resolvedLinks;
        }

        return response()->json($envelope, $statusCode);
    }

    /**
     * 201 Created - resource was successfully persisted.
     * Attaches a Location header pointing to the canonical resource URI.
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully',
        ?string $locationUrl = null,
        array $meta = []
    ): JsonResponse {
        $response = self::success($data, $message, $meta, [], 201);

        if ($locationUrl) {
            $response->header('Location', $locationUrl);
        }

        return $response;
    }

    /**
     * 200 with deletion confirmation.
     * Returns 200 instead of 204 so the standard envelope is preserved for all clients.
     */
    public static function deleted(
        string $message = 'Resource deleted successfully'
    ): JsonResponse {
        return self::success(null, $message, [], [], 200);
    }

    /**
     * 202 Accepted - async job queued, not yet processed.
     */
    public static function accepted(
        mixed $data = null,
        string $message = 'Request accepted and queued for processing'
    ): JsonResponse {
        return self::success($data, $message, [], [], 202);
    }

    // =========================================================================
    // ERROR RESPONSES
    // =========================================================================

    /**
     * Build a standard error response envelope (RFC 7807 inspired).
     *
     * @param  string  $errorCode  Machine-readable slug (SCREAMING_SNAKE_CASE)
     * @param  string  $message  Human-readable summary
     * @param  mixed  $detail  Structured detail block (field errors, resource info, etc.)
     * @param  int  $statusCode  HTTP status code
     * @param  bool  $retryAllowed  Whether the client should retry the request
     * @param  int|null  $retryAfterSeconds  Seconds before a safe retry (sets Retry-After header)
     * @param  array|null  $debug  Debug payload - only rendered when APP_DEBUG=true
     */
    public static function error(
        string $errorCode = 'INTERNAL_SERVER_ERROR',
        string $message = 'An unexpected error occurred.',
        mixed $detail = null,
        int $statusCode = 500,
        bool $retryAllowed = false,
        ?int $retryAfterSeconds = null,
        ?array $debug = null
    ): JsonResponse {
        $requestId = self::resolveRequestId();
        $processingTime = self::processingTimeMs();

        $envelope = [
            'success' => false,
            'status_code' => $statusCode,
            'request_id' => $requestId,
            'timestamp' => now()->toISOString(),
            'error' => [
                'code' => $errorCode,
                'type' => self::errorType($statusCode, $errorCode),
                'message' => $message,
                'detail' => $detail,
                'debug' => (config('app.debug') && $debug !== null) ? $debug : null,
            ],
            'meta' => [
                'system' => config('api.system_name', 'OutfitShop Ecommerce Clothing API'),
                'api_version' => config('api.version', '1.0.0'),
                'processing_time_ms' => $processingTime,
                'documentation' => config('api.docs_base', '/api/v1/guide').'#'.strtolower($errorCode),
                'retry_allowed' => $retryAllowed,
                'retry_after_seconds' => $retryAfterSeconds,
                'support_contact' => config('api.support_email', 'support@kesararamwithdigital.tech'),
            ],
        ];

        $response = response()->json($envelope, $statusCode);

        if ($retryAfterSeconds !== null) {
            $response->header('Retry-After', (string) $retryAfterSeconds);
            $response->header('X-RateLimit-Reset', (string) (time() + $retryAfterSeconds));
        }

        return $response;
    }

    // =========================================================================
    // TYPED ERROR SHORTCUTS
    // =========================================================================

    /**
     * 401 - Token missing, invalid, or expired.
     */
    public static function unauthenticated(
        string $reason = 'token_missing',
        string $message = 'Invalid or expired authentication token.',
        ?string $expiredAt = null
    ): JsonResponse {
        return self::error(
            'AUTHENTICATION_FAILED',
            $message,
            array_filter([
                'reason' => $reason,
                'token_type' => 'Bearer',
                'token_expired_at' => $expiredAt,
                'reauthenticate_endpoint' => '/api/v1/auth/login',
                'refresh_endpoint' => '/api/v1/auth/refresh',
            ]),
            401,
            true
        );
    }

    /**
     * 403 - Authenticated but lacks sufficient permission.
     */
    public static function forbidden(
        string $message = 'You do not have permission to perform this action.'
    ): JsonResponse {
        return self::error(
            'FORBIDDEN_ACCESS',
            $message,
            ['required_role' => 'Contact your administrator to request elevated access.'],
            403,
            false
        );
    }

    /**
     * 404 - Resource was not found.
     */
    public static function notFound(
        string $resource = 'Resource',
        mixed $identifier = null,
        string $message = 'The requested resource was not found.'
    ): JsonResponse {
        return self::error(
            'RESOURCE_NOT_FOUND',
            $message,
            array_filter([
                'resource' => $resource,
                'identifier' => $identifier,
            ]),
            404,
            false
        );
    }

    /**
     * 405 - HTTP method not supported on this endpoint.
     */
    public static function methodNotAllowed(
        string $method = '',
        string $endpoint = ''
    ): JsonResponse {
        return self::error(
            'METHOD_NOT_ALLOWED',
            'The HTTP method used is not allowed for this endpoint.',
            array_filter([
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'hint' => 'Check the API documentation for supported HTTP methods.',
            ]),
            405,
            false
        );
    }

    /**
     * 409 - State conflict (duplicate resource, already-processed idempotent key, etc.).
     */
    public static function conflict(
        string $message,
        string $errorCode = 'RESOURCE_CONFLICT',
        mixed $detail = null
    ): JsonResponse {
        return self::error($errorCode, $message, $detail, 409, true);
    }

    /**
     * 422 - Validation failed with per-field detail.
     */
    public static function validationError(
        array $errors = [],
        string $message = 'The provided data failed validation.'
    ): JsonResponse {
        $fields = [];
        foreach ($errors as $field => $messages) {
            $fields[] = [
                'field' => $field,
                'rule' => 'validation',
                'message' => $messages[0] ?? 'Invalid value.',
            ];
        }

        return self::error(
            'VALIDATION_ERROR',
            $message,
            ['fields' => $fields],
            422,
            true
        );
    }

    /**
     * 423 - Account locked (brute-force protection trigger).
     */
    public static function accountLocked(
        string $message = 'Account temporarily locked due to excessive failed login attempts.',
        int $retryAfter = 900
    ): JsonResponse {
        return self::error(
            'ACCOUNT_LOCKED',
            $message,
            [
                'reason' => 'too_many_failed_attempts',
                'retry_after_seconds' => $retryAfter,
                'contact' => config('api.support_email', 'support@kesararamwithdigital.tech'),
            ],
            423,
            true,
            $retryAfter
        );
    }

    /**
     * 429 - Rate limit exceeded.
     */
    public static function tooManyRequests(
        int $limit = 60,
        int $remaining = 0,
        int $retryAfter = 60,
        string $role = 'PUBLIC'
    ): JsonResponse {
        return self::error(
            'RATE_LIMIT_EXCEEDED',
            'Too many requests. Please retry after the specified time.',
            [
                'limit' => $limit,
                'remaining' => $remaining,
                'reset_at' => now()->addSeconds($retryAfter)->toISOString(),
                'retry_after_seconds' => $retryAfter,
                'window' => '1 minute',
                'role_limit' => $role,
            ],
            429,
            true,
            $retryAfter
        );
    }

    /**
     * 500 - Unhandled server exception.
     */
    public static function serverError(
        string $message = 'An unexpected error occurred while processing the request.',
        ?array $debug = null
    ): JsonResponse {
        return self::error(
            'INTERNAL_SERVER_ERROR',
            $message,
            null,
            500,
            true,
            5,
            $debug
        );
    }

    /**
     * 503 - Service temporarily unavailable (maintenance, circuit breaker open).
     */
    public static function serviceUnavailable(
        string $message = 'The service is temporarily unavailable.',
        int $retryAfter = 30
    ): JsonResponse {
        return self::error(
            'SERVICE_UNAVAILABLE',
            $message,
            null,
            503,
            true,
            $retryAfter
        );
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Resolve the request ID: echo the client header when provided,
     * otherwise generate a fresh UUID v4.
     */
    private static function resolveRequestId(): string
    {
        try {
            $headerValue = request()->header('X-Request-Id');

            return ($headerValue && strlen($headerValue) <= 64)
                ? $headerValue
                : (string) Str::uuid();
        } catch (\Throwable) {
            return (string) Str::uuid();
        }
    }

    /**
     * Calculate processing time in milliseconds from LARAVEL_START constant.
     */
    private static function processingTimeMs(): int
    {
        $start = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        return (int) round((microtime(true) - $start) * 1000);
    }

    /**
     * Build pagination meta block from a LengthAwarePaginator.
     */
    private static function buildPaginationMeta(LengthAwarePaginator $paginator, array $filters = []): array
    {
        $meta = [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'has_next' => $paginator->hasMorePages(),
                'has_previous' => $paginator->currentPage() > 1,
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'next_cursor' => $paginator->hasMorePages()
                    ? base64_encode(json_encode(['page' => $paginator->currentPage() + 1]))
                    : null,
                'previous_cursor' => $paginator->currentPage() > 1
                    ? base64_encode(json_encode(['page' => $paginator->currentPage() - 1]))
                    : null,
            ],
        ];

        if (! empty($filters)) {
            $meta['filters_applied'] = $filters;
        }

        return $meta;
    }

    /**
     * Build pagination links block from a LengthAwarePaginator.
     */
    private static function buildPaginationLinks(LengthAwarePaginator $paginator): array
    {
        return [
            'self' => $paginator->url($paginator->currentPage()),
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'previous' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }

    /**
     * Build cursor pagination meta block.
     */
    private static function buildCursorMeta(CursorPaginator $paginator): array
    {
        return [
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'has_next' => $paginator->hasMorePages(),
                'has_previous' => $paginator->previousCursor() !== null,
            ],
        ];
    }

    /**
     * Map HTTP status code and error code to a human-readable exception type label.
     */
    private static function errorType(int $statusCode, string $errorCode = ''): string
    {
        if ($errorCode === 'VALIDATION_ERROR') {
            return 'ValidationException';
        }
        if ($errorCode === 'RATE_LIMIT_EXCEEDED') {
            return 'ThrottleRequestsException';
        }
        if ($errorCode === 'RESOURCE_NOT_FOUND') {
            return 'ModelNotFoundException';
        }
        if ($errorCode === 'ACCOUNT_LOCKED') {
            return 'AccountLockedException';
        }

        return match ($statusCode) {
            401 => 'AuthenticationException',
            403 => 'AuthorizationException',
            404 => 'ModelNotFoundException',
            405 => 'MethodNotAllowedException',
            409 => 'ConflictException',
            422 => 'ValidationException',
            423 => 'AccountLockedException',
            429 => 'ThrottleRequestsException',
            500 => 'RuntimeException',
            503 => 'ServiceUnavailableException',
            504 => 'GatewayTimeoutException',
            default => 'Exception',
        };
    }
}
