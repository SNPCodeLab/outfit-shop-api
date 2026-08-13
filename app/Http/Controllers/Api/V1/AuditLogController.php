<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;

class AuditLogController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $logs = AuditLog::orderBy('created_at', 'desc')->paginate(50);
        return $this->successResponse($logs, 'System audit logs');
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::findOrFail($id);
        return $this->successResponse($log, 'Audit log entry details');
    }
}
