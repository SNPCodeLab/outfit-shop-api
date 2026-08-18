<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PosShift;
use App\Models\SaleHeader;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosShiftController extends BaseApiController
{
    /**
     * Get active open shift for current cashier
     */
    public function current(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;

        $shift = PosShift::where('employee_id', $employeeId)
            ->where('status', 'OPEN')
            ->latest('opened_at')
            ->first();

        return $this->successResponse([
            'shift' => $shift,
            'is_open' => (bool) $shift,
        ], $shift ? 'Active shift found' : 'No active shift currently open');
    }

    /**
     * Open a new cashier shift with opening cash float
     */
    public function open(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;

        // Check if shift is already open
        $existing = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->first();
        if ($existing) {
            return $this->conflictResponse(
                'You already have an open shift. Please close it before opening a new one.',
                'SHIFT_ALREADY_OPEN',
                ['active_shift_id' => $existing->shift_id]
            );
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:store_branches,branch_id',
            'opening_float_usd' => 'required|numeric|min:0',
            'opening_float_khr' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift = PosShift::create([
            'employee_id' => $employeeId,
            'branch_id' => $validated['branch_id'] ?? null,
            'opened_at' => Carbon::now(),
            'opening_float_usd' => $validated['opening_float_usd'],
            'opening_float_khr' => $validated['opening_float_khr'] ?? ($validated['opening_float_usd'] * 4100),
            'status' => 'OPEN',
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->createdResponse($shift, 'POS Register Shift opened successfully', '/api/v1/shifts/current');
    }

    /**
     * Record a petty cash drop / safe drop during the shift
     */
    public function dropCash(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;
        $shift = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->first();

        if (! $shift) {
            return $this->notFoundResponse('PosShift', null, 'No active open shift found for cash drop.');
        }

        $validated = $request->validate([
            'drop_amount_usd' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $shift->increment('petty_cash_drops_usd', $validated['drop_amount_usd']);

        return $this->successResponse($shift->fresh(), "Petty cash drop of \${$validated['drop_amount_usd']} recorded");
    }

    /**
     * Close shift, count physical cash, compute discrepancy, and generate Z-Report.
     * Eager-loads payments to prevent N+1 query performance bottleneck.
     */
    public function close(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;
        $shift = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->first();

        if (! $shift) {
            return $this->notFoundResponse('PosShift', null, 'No active open shift found to close.');
        }

        $validated = $request->validate([
            'closing_cash_usd' => 'required|numeric|min:0',
            'closing_cash_khr' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate sales during this shift window with eager-loaded payments (Eager Loading N+1 fix)
        $sales = SaleHeader::with('payments')
            ->where('employee_id', $employeeId)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'COMPLETED')
            ->get();

        $totalCashSales = 0.0;
        $totalCardSales = 0.0;
        $totalQrSales = 0.0;

        foreach ($sales as $sale) {
            foreach ($sale->payments as $p) {
                $method = strtoupper($p->payment_method);
                if ($method === 'CASH') {
                    $totalCashSales += (float) $p->amount;
                } elseif ($method === 'CARD') {
                    $totalCardSales += (float) $p->amount;
                } else {
                    $totalQrSales += (float) $p->amount;
                }
            }
        }

        $expectedCash = (float) $shift->opening_float_usd + $totalCashSales - (float) $shift->petty_cash_drops_usd;
        $actualCash = (float) $validated['closing_cash_usd'];
        $discrepancy = round($actualCash - $expectedCash, 2);

        $zReport = [
            'shift_id' => $shift->shift_id,
            'opened_at' => $shift->opened_at->toISOString(),
            'closed_at' => now()->toISOString(),
            'opening_float' => (float) $shift->opening_float_usd,
            'total_sales_count' => $sales->count(),
            'cash_sales' => round($totalCashSales, 2),
            'card_sales' => round($totalCardSales, 2),
            'qr_sales' => round($totalQrSales, 2),
            'gross_revenue' => round((float) $sales->sum('grand_total'), 2),
            'petty_cash_drops' => (float) $shift->petty_cash_drops_usd,
            'expected_cash' => round($expectedCash, 2),
            'actual_cash' => round($actualCash, 2),
            'discrepancy' => $discrepancy,
            'discrepancy_status' => $discrepancy === 0.0 ? 'BALANCED' : ($discrepancy > 0 ? 'OVER' : 'SHORT'),
        ];

        $shift->update([
            'closed_at' => now(),
            'cash_sales_usd' => $totalCashSales,
            'card_sales_usd' => $totalCardSales,
            'qr_sales_usd' => $totalQrSales,
            'expected_cash_usd' => $expectedCash,
            'closing_cash_usd' => $actualCash,
            'discrepancy_usd' => $discrepancy,
            'status' => 'CLOSED',
            'z_report_summary' => $zReport,
        ]);

        return $this->successResponse([
            'shift' => $shift->fresh(),
            'z_report' => $zReport,
        ], 'Shift closed successfully. End-of-Day Z-Report generated.');
    }
}
