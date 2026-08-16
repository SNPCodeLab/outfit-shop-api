<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Return a standard success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return a standard error JSON response.
     *
     * @param  string       $message    Human-readable error description
     * @param  int          $code       HTTP status code
     * @param  string|null  $errorCode  Machine-readable error code (e.g. ERR_FORBIDDEN)
     * @param  mixed        $errors     Validation error bag or extra detail
     */
    protected function errorResponse(
        string  $message   = 'Error',
        int     $code      = 400,
        ?string $errorCode = null,
        mixed   $errors    = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
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
