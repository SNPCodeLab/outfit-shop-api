<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Record an audit event.
     *
     * Wrapped in try/catch so that a failed audit write never crashes the
     * parent DB transaction. Failures are silently logged to Laravel's error
     * log for alerting / monitoring (Graceful Degradation pattern).
     *
     * @param  string  $action  LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SALE, VOID_SALE,
     *                          PURCHASE, ADJUSTMENT, CONVERT_ESTIMATE_TO_INVOICE, BROADCAST
     * @param  string  $entity  Model/Resource name (e.g. 'SaleHeader', 'ProductVariant')
     * @param  mixed  $entityId  Primary key of the target resource
     * @param  array|null  $oldValues  Previous field values (for UPDATE / VOID_SALE)
     * @param  array|null  $newValues  New field values
     * @param  int|null  $userId  Override authenticated user ID
     * @return AuditLog|null Returns null on failure (never throws)
     */
    public static function log(
        string $action,
        string $entity,
        mixed $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): ?AuditLog {
        try {
            $currentUser = null;
            try {
                $currentUser = auth('sanctum')->user() ?? auth()->user();
            } catch (\Throwable) {
                // Ignore auth failures during logging
            }

            return AuditLog::create([
                'user_id' => $userId ?? $currentUser?->employee_id ?? $currentUser?->id,
                'user_type' => $currentUser ? get_class($currentUser) : 'Guest',
                'action' => strtoupper($action),
                'entity' => $entity,
                'entity_id' => (string) $entityId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);
        } catch (\Throwable $e) {
            // Graceful degradation: audit failure must NEVER crash a business transaction.
            Log::error('[AuditLogService] Failed to write audit record', [
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
