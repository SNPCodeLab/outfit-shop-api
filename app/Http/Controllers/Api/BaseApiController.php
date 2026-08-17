<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

abstract class BaseApiController extends Controller
{
    /**
     * Return a standard success JSON response.
     *
     * Every response includes a unique `request_id` (UUID v4) for distributed
     * tracing and observability (Correlation ID pattern).
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => $message,
            'data'       => $data,
            'request_id' => (string) Str::uuid(),
        ], $code);
    }

    /**
     * Return a standard error JSON response.
     *
     * @param  string       $message    Human-readable error description
     * @param  int          $code       HTTP status code (400, 401, 403, 404, 422, 500)
     * @param  string|null  $errorCode  Machine-readable error code (e.g. ERR_INSUFFICIENT_STOCK)
     * @param  mixed        $errors     Validation error bag or extra detail
     */
    protected function errorResponse(
        string  $message   = 'Error',
        int     $code      = 400,
        ?string $errorCode = null,
        mixed   $errors    = null
    ): JsonResponse {
        $response = [
            'success'    => false,
            'message'    => $message,
            'request_id' => (string) Str::uuid(),
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
