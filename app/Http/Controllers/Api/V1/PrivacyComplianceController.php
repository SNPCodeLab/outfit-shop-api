<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrivacyComplianceController extends BaseApiController
{
    /**
     * POST /api/v1/compliance/customers/{id}/export-data
     * GDPR Article 20: Right to Data Portability (Export all customer records as structured JSON).
     */
    public function exportData(int $id): JsonResponse
    {
        $customer = Customer::with(['sales.details.variant.product', 'sales.payments', 'wishlist.variant.product'])
            ->findOrFail($id);

        AuditLogService::log('GDPR_EXPORT', 'Customer', $id, null, ['exported_at' => now()->toISOString()]);

        return $this->successResponse([
            'compliance_standard' => 'GDPR Article 20 / Cambodian Data Protection Directive',
            'exported_at' => now()->toISOString(),
            'profile' => [
                'customer_id' => $customer->customer_id,
                'customer_name' => $customer->customer_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'loyalty_points' => $customer->loyalty_points,
                'member_tier' => $customer->member_tier,
                'created_at' => $customer->created_at?->toISOString(),
            ],
            'purchase_history' => $customer->sales,
            'wishlist' => $customer->wishlist,
        ], 'Customer personal data archive generated for download');
    }

    /**
     * POST /api/v1/compliance/customers/{id}/forget-me
     * GDPR Article 17: Right to Erasure (Anonymize PII while preserving financial ledgers for 7-year tax compliance).
     */
    public function forgetMe(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        DB::transaction(function () use ($customer, $id) {
            $anonymousName = 'Anonymized Customer #'.$id;

            $customer->update([
                'customer_name' => $anonymousName,
                'email' => "anonymized_{$id}@deleted-user.invalid",
                'phone' => null,
                'address' => null,
                'loyalty_points' => 0,
            ]);

            AuditLogService::log(
                action: 'GDPR_ERASURE',
                entity: 'Customer',
                entityId: $id,
                newValues: ['status' => 'ANONYMIZED_PII_REMOVED']
            );
        });

        return $this->successResponse([
            'customer_id' => $id,
            'status' => 'ANONYMIZED',
            'tax_ledger_status' => 'Financial invoice amounts preserved for statutory 7-year audit retention',
        ], 'Customer personally identifiable information (PII) successfully erased');
    }

    /**
     * GET /api/v1/compliance/audit-retention-policy
     * Returns statutory 7-year audit retention policy & PCI-DSS compliance certification.
     */
    public function policy(): JsonResponse
    {
        return $this->successResponse([
            'compliance_frameworks' => [
                'GDPR' => [
                    'status' => 'COMPLIANT',
                    'data_controller' => 'CSMS Retail Operations Ltd.',
                    'portability_supported' => true,
                    'erasure_supported' => true,
                    'dpo_contact' => 'privacy@kesararamwithdigital.tech',
                ],
                'PCI_DSS' => [
                    'status' => 'LEVEL_4_MERCHANT_COMPLIANT',
                    'card_storage_policy' => 'ZERO_CARD_STORAGE (Full tokenization via ABA PayWay / KHQR / Stripe Gateway)',
                    'cvv_storage' => 'NEVER_STORED',
                    'pan_storage' => 'NEVER_STORED',
                ],
                'AUDIT_RETENTION' => [
                    'financial_ledgers_retention_years' => 7,
                    'stock_movement_retention_years' => 7,
                    'system_api_logs_retention_days' => 90,
                    'database_daily_backup_retention_days' => 30,
                ],
            ],
        ], 'Enterprise compliance & 7-year audit retention policy retrieved');
    }
}
