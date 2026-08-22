<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::orderBy('created_at', 'desc')
            ->paginate($this->perPage($request, 50));

        return $this->successResponse($logs, 'System audit logs');
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::findOrFail($id);

        return $this->successResponse($log, 'Audit log entry details');
    }
}
