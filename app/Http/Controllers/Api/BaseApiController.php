<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * BaseApiController
 *
 * Thin delegation layer so all API controllers share a
 * consistent method signature while ApiResponse handles
 * all envelope construction logic.
 */
abstract class BaseApiController extends Controller
{
    // =========================================================================
    // SUCCESS DELEGATES
    // =========================================================================

    protected function successResponse(
        mixed  $data       = null,
        string $message    = 'Operation completed successfully',
        int    $code       = 200,
        array  $meta       = [],
        array  $filters    = []
    ): JsonResponse {
        return ApiResponse::success($data, $message, $meta, [], $code, $filters);
    }

    protected function createdResponse(
        mixed   $data        = null,
        string  $message     = 'Resource created successfully',
        ?string $locationUrl = null,
        array   $meta        = []
    ): JsonResponse {
        return ApiResponse::created($data, $message, $locationUrl, $meta);
    }

    protected function deletedResponse(
        string $message = 'Resource deleted successfully'
    ): JsonResponse {
        return ApiResponse::deleted($message);
    }

    protected function acceptedResponse(
        mixed  $data    = null,
        string $message = 'Request accepted and queued for processing'
    ): JsonResponse {
        return ApiResponse::accepted($data, $message);
    }

    // =========================================================================
    // ERROR DELEGATES
    // =========================================================================

    protected function errorResponse(
        string  $message          = 'An error occurred.',
        int     $code             = 400,
        ?string $errorCode        = null,
        mixed   $errors           = null,
        ?string $hint             = null
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
        array  $errors  = [],
        string $message = 'The provided data failed validation.'
    ): JsonResponse {
        return ApiResponse::validationError($errors, $message);
    }

    protected function unauthorizedResponse(
        string $reason  = 'token_missing',
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
        string $resource   = 'Resource',
        mixed  $identifier = null,
        string $message    = 'The requested resource was not found.'
    ): JsonResponse {
        return ApiResponse::notFound($resource, $identifier, $message);
    }

    protected function conflictResponse(
        string $message,
        string $errorCode = 'RESOURCE_CONFLICT',
        mixed  $detail    = null
    ): JsonResponse {
        return ApiResponse::conflict($message, $errorCode, $detail);
    }

    protected function tooManyRequestsResponse(
        int    $limit      = 60,
        int    $remaining  = 0,
        int    $retryAfter = 60
    ): JsonResponse {
        return ApiResponse::tooManyRequests($limit, $remaining, $retryAfter);
    }

    protected function serverErrorResponse(
        string $message = 'An unexpected error occurred.',
        ?array $debug   = null
    ): JsonResponse {
        return ApiResponse::serverError($message, $debug);
    }
}
