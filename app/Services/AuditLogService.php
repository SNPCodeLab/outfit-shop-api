<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Record an audit event.
     *
     * @param string $action LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SALE, PURCHASE, ADJUSTMENT
     * @param string $entity Model/Resource name
     * @param mixed $entityId ID of the target resource
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $userId
     * @return AuditLog
     */
    public static function log(
        string $action,
        string $entity,
        mixed $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $currentUser = auth('sanctum')->user() ?? auth()->user();

        return AuditLog::create([
            'user_id'    => $userId ?? $currentUser?->employee_id ?? $currentUser?->id,
            'user_type'  => $currentUser ? get_class($currentUser) : 'Guest',
            'action'     => strtoupper($action),
            'entity'     => $entity,
            'entity_id'  => (string) $entityId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
