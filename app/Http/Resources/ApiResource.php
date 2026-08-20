<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base API resource.
 *
 * Laravel's default `data` wrap is disabled because this project already
 * wraps every payload in App\Http\Response\ApiResponse.
 *
 * Existing V1 controllers may keep returning arrays/models. Adopt a
 * dedicated Resource class when a response shape needs to be stable
 * for frontend clients.
 */
abstract class ApiResource extends JsonResource
{
    public static $wrap = null;
}
