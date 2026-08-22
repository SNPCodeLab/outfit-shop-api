<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BaseApiController
 *
 * Thin delegation layer so all API controllers share a
 * consistent method signature while ApiResponse handles
 * all envelope construction logic.
 */
abstract class BaseApiController extends Controller
{
    /**
     * Maximum page size accepted from clients on any paginated list
     * endpoint. Prevents page-size denial-of-service (a per_page of
     * 100000 dumping the full sales ledger with eager loads).
     */
    protected const MAX_PER_PAGE = 100;

    /**
     * Resolve a sanitized page size from the request's per_page parameter:
     * falls back to the default when absent or invalid, and caps at 100.
     */
    protected function perPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->input('per_page', $default);

        if ($perPage < 1) {
            $perPage = $default;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * Escape LIKE/ILIKE wildcards so client search input matches literally
     * (blocks wildcard-injection patterns such as "%%" forcing full scans).
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    // =========================================================================
    // SUCCESS DELEGATES
    // =========================================================================

    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        int $code = 200,
        array $meta = [],
        array $filters = []
    ): JsonResponse {
        return ApiResponse::success($data, $message, $meta, [], $code, $filters);
    }

    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully',
        ?string $locationUrl = null,
        array $meta = []
    ): JsonResponse {
        return ApiResponse::created($data, $message, $locationUrl, $meta);
    }

    protected function deletedResponse(
        string $message = 'Resource deleted successfully'
    ): JsonResponse {
        return ApiResponse::deleted($message);
    }

    protected function acceptedResponse(
        mixed $data = null,
        string $message = 'Request accepted and queued for processing'
    ): JsonResponse {
        return ApiResponse::accepted($data, $message);
    }

    // =========================================================================
    // ERROR DELEGATES
    // =========================================================================

    protected function errorResponse(
        string $message = 'An error occurred.',
        int $code = 400,
        ?string $errorCode = null,
        mixed $errors = null,
        ?string $hint = null
    ): JsonResponse {
        return ApiResponse::error(
            $errorCode ?? 'BAD_REQUEST',
            $message,
            $errors,
            $code,
            $code >= 500 || in_array($code, [422, 429]),
            $code === 429 ? 60 : null
        );
    }

    protected function validationErrorResponse(
        array $errors = [],
        string $message = 'The provided data failed validation.'
    ): JsonResponse {
        return ApiResponse::validationError($errors, $message);
    }

    protected function unauthorizedResponse(
        string $reason = 'token_missing',
        string $message = 'Invalid or expired authentication token.'
    ): JsonResponse {
        return ApiResponse::unauthenticated($reason, $message);
    }

    protected function forbiddenResponse(
        string $message = 'You do not have permission to perform this action.'
    ): JsonResponse {
        return ApiResponse::forbidden($message);
    }

    protected function notFoundResponse(
        string $resource = 'Resource',
        mixed $identifier = null,
        string $message = 'The requested resource was not found.'
    ): JsonResponse {
        return ApiResponse::notFound($resource, $identifier, $message);
    }

    protected function conflictResponse(
        string $message,
        string $errorCode = 'RESOURCE_CONFLICT',
        mixed $detail = null
    ): JsonResponse {
        return ApiResponse::conflict($message, $errorCode, $detail);
    }

    protected function tooManyRequestsResponse(
        int $limit = 60,
        int $remaining = 0,
        int $retryAfter = 60
    ): JsonResponse {
        return ApiResponse::tooManyRequests($limit, $remaining, $retryAfter);
    }

    protected function serverErrorResponse(
        string $message = 'An unexpected error occurred.',
        ?array $debug = null
    ): JsonResponse {
        return ApiResponse::serverError($message, $debug);
    }
}
