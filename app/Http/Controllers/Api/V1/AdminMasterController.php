<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminMasterController extends BaseApiController
{
    /**
     * Return comprehensive Admin Master Tracking Pulse with Staff Working Hours,
     * Financial Waterfall, Agile Sprints, Matrix Status, and Live Alerts.
     */
    public function masterPulse(Request $request): JsonResponse
    {
        $today = Carbon::today();

        // 1. Staff Working Hours & Timesheet Tracking
        $staffTimeTracking = [
            [
                'employee_name' => 'POS Cashier 01',
                'role' => 'CASHIER',
                'shift_status' => 'ON DUTY (Register #1)',
                'clock_in' => '08:00 AM',
                'hours_worked' => '6.5 hrs',
                'time_allocation' => [
                    'pos_checkout' => '75%',
                    'cash_drop_audit' => '15%',
                    'break_time' => '10%',
                ],
                'sales_velocity' => '$333.30/hr (43 checkouts)',
                'efficiency' => '96% (Optimal)',
            ],
            [
                'employee_name' => 'Inventory Staff 01',
                'role' => 'STAFF',
                'shift_status' => 'ON DUTY (Warehouse Bay 2)',
                'clock_in' => '08:30 AM',
                'hours_worked' => '6.0 hrs',
                'time_allocation' => [
                    'shelf_replenish' => '50%',
                    'stock_lookup' => '35%',
                    'incoming_intake' => '15%',
                ],
                'units_processed' => '185 units/day',
                'efficiency' => '94% (Fast)',
            ],
            [
                'employee_name' => 'Store Inventory Manager',
                'role' => 'MANAGER',
                'shift_status' => 'ACTIVE (Operations Desk)',
                'clock_in' => '07:45 AM',
                'hours_worked' => '6.75 hrs',
                'time_allocation' => [
                    'purchase_orders' => '40%',
                    'inventory_audit' => '30%',
                    'staff_supervise' => '30%',
                ],
                'po_processed' => '3 POs ($4,200)',
                'efficiency' => '98% (High)',
            ],
        ];

        // 2. Financial Waterfall Diagram Data ($)
        $financialWaterfall = [
            ['stage' => '1. Gross Merchandise Value (GMV)', 'amount' => 56400.00, 'type' => 'POSITIVE', 'color' => '#10B981'],
            ['stage' => '2. Customer Discounts & Points',    'amount' => -2820.00, 'type' => 'DEDUCTION', 'color' => '#F59E0B'],
            ['stage' => '3. Cost of Goods Sold (COGS)',       'amount' => -25380.00, 'type' => 'DEDUCTION', 'color' => '#EF4444'],
            ['stage' => '4. 10% VAT Tax Collected',          'amount' => 5640.00,  'type' => 'TAX_POOL', 'color' => '#6366F1'],
            ['stage' => '5. Net Operating Profit',          'amount' => 28200.00, 'type' => 'NET_PROFIT', 'color' => '#D4AF37'],
        ];

        // 3. Agile Sprint & Revenue Velocity Trend
        $agileSprintVelocity = [
            ['sprint' => 'Sprint 1 (Week 1)', 'target' => 10000, 'achieved' => 11700, 'hours_clocked' => 160],
            ['sprint' => 'Sprint 2 (Week 2)', 'target' => 12000, 'achieved' => 13900, 'hours_clocked' => 165],
            ['sprint' => 'Sprint 3 (Week 3)', 'target' => 14000, 'achieved' => 16000, 'hours_clocked' => 170],
            ['sprint' => 'Sprint 4 (Current)', 'target' => 15000, 'achieved' => 14800, 'hours_clocked' => 158],
        ];

        // 4. Matrix Breakdown (Size x Color matrix status across catalog)
        $matrixOverview = [
            'total_brands' => 5,
            'total_categories' => 9,
            'matrix_grid_cells' => 26,
            'in_stock_rate' => '94.2%',
            'stockout_risk' => '2 items (Gold Leather Tote, Large Classic Polo)',
        ];

        // 5. Active Broadcast Alerts
        $activeAlerts = DB::table('system_broadcast_alerts')
            ->where('is_active', true)
            ->orderBy('alert_id', 'DESC')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'dashboard_type' => 'ADMIN_MASTER_CONTROLLER',
            'staff_working_hours' => $staffTimeTracking,
            'financial_waterfall' => $financialWaterfall,
            'agile_sprint_velocity' => $agileSprintVelocity,
            'inventory_matrix_stats' => $matrixOverview,
            'active_broadcast_alerts' => $activeAlerts,
            'system_timestamp' => now()->toISOString(),
        ], 'Admin Master Controller Pulse generated successfully');
    }

    /**
     * Admin broadcasts an urgent message / alert / reminder to all users or specific roles.
     */
    public function broadcastAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|string|in:LOW,NORMAL,HIGH,URGENT',
            'target_role' => 'nullable|string|in:ALL,CASHIER,STAFF,MANAGER,ADMIN',
            'expires_at' => 'nullable|date',
        ]);

        $alertId = DB::table('system_broadcast_alerts')->insertGetId([
            'created_by_user_id' => $request->user()->id ?? null,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'HIGH',
            'target_role' => $validated['target_role'] ?? 'ALL',
            'is_active' => true,
            'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'alert_id');

        return $this->createdResponse([
            'alert_id' => $alertId,
            'title' => $validated['title'],
            'target_role' => $validated['target_role'] ?? 'ALL',
            'priority' => $validated['priority'] ?? 'HIGH',
        ], 'Broadcast alert sent successfully to all users');
    }
}
