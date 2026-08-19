<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\AuthController as V1AuthController;

/**
 * Backward Compatibility Proxy for Root AuthController
 */
class AuthController extends V1AuthController {}
