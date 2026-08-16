<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PosShift;
use App\Models\SaleHeader;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosShiftController extends Controller
{
    /**
     * Get active open shift for current cashier
     */
    public function current(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? 1;

        $shift = PosShift::where('employee_id', $employeeId)
            ->where('status', 'OPEN')
            ->latest('opened_at')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $shift,
            'is_open' => (bool) $shift,
            'message' => $shift ? 'Active shift found' : 'No active shift currently open',
        ]);
    }

    /**
     * Open a new cashier shift with opening cash float
     */
    public function open(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? 1;

        // Check if shift is already open
        $existing = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an open shift. Please close it before opening a new one.',
            ], 400);
        }

        $validated = $request->validate([
            'branch_id'         => 'nullable|exists:store_branches,branch_id',
            'opening_float_usd' => 'required|numeric|min:0',
            'opening_float_khr' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        $shift = PosShift::create([
            'employee_id'       => $employeeId,
            'branch_id'         => $validated['branch_id'] ?? null,
            'opened_at'         => Carbon::now(),
            'opening_float_usd' => $validated['opening_float_usd'],
            'opening_float_khr' => $validated['opening_float_khr'] ?? ($validated['opening_float_usd'] * 4100),
            'status'            => 'OPEN',
            'notes'             => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $shift,
            'message' => 'POS Register Shift opened successfully',
        ], 201);
    }

    /**
     * Record a petty cash drop / safe drop during the shift
     */
    public function dropCash(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? 1;
        $shift = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->firstOrFail();

        $validated = $request->validate([
            'drop_amount_usd' => 'required|numeric|min:0.01',
            'reason'          => 'nullable|string|max:255',
        ]);

        $shift->increment('petty_cash_drops_usd', $validated['drop_amount_usd']);

        return response()->json([
            'success' => true,
            'data'    => $shift,
            'message' => "Petty cash drop of \${$validated['drop_amount_usd']} recorded",
        ]);
    }

    /**
     * Close shift, count physical cash, compute discrepancy, and generate Z-Report
     */
    public function close(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id ?? 1;
        $shift = PosShift::where('employee_id', $employeeId)->where('status', 'OPEN')->firstOrFail();

        $validated = $request->validate([
            'closing_cash_usd' => 'required|numeric|min:0',
            'closing_cash_khr' => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        // Calculate sales during this shift window
        $sales = SaleHeader::where('employee_id', $employeeId)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'COMPLETED')
            ->get();

        $totalCashSales = 0.0;
        $totalCardSales = 0.0;
        $totalQrSales = 0.0;

        foreach ($sales as $sale) {
            foreach ($sale->payments as $p) {
                if ($p->payment_method === 'CASH') $totalCashSales += (float)$p->amount;
                elseif ($p->payment_method === 'CARD') $totalCardSales += (float)$p->amount;
                else $totalQrSales += (float)$p->amount;
            }
        }

        $expectedCash = $shift->opening_float_usd + $totalCashSales - $shift->petty_cash_drops_usd;
        $discrepancy = $validated['closing_cash_usd'] - $expectedCash;

        $zReport = [
            'shift_id'           => $shift->shift_id,
            'opened_at'          => $shift->opened_at->toIso8601String(),
            'closed_at'          => now()->toIso8601String(),
            'opening_float'      => $shift->opening_float_usd,
            'total_sales_count'  => $sales->count(),
            'cash_sales'         => $totalCashSales,
            'card_sales'         => $totalCardSales,
            'qr_sales'           => $totalQrSales,
            'gross_revenue'      => $sales->sum('grand_total'),
            'petty_cash_drops'   => $shift->petty_cash_drops_usd,
            'expected_cash'      => $expectedCash,
            'actual_cash'        => (float) $validated['closing_cash_usd'],
            'discrepancy'        => $discrepancy,
            'discrepancy_status' => $discrepancy === 0.0 ? 'BALANCED' : ($discrepancy > 0 ? 'OVER' : 'SHORT'),
        ];

        $shift->update([
            'closed_at'         => now(),
            'cash_sales_usd'    => $totalCashSales,
            'card_sales_usd'    => $totalCardSales,
            'qr_sales_usd'      => $totalQrSales,
            'expected_cash_usd' => $expectedCash,
            'closing_cash_usd'  => $validated['closing_cash_usd'],
            'discrepancy_usd'   => $discrepancy,
            'status'            => 'CLOSED',
            'z_report_summary'  => $zReport,
        ]);

        return response()->json([
            'success'  => true,
            'data'     => $shift,
            'z_report' => $zReport,
            'message'  => 'Shift closed successfully. End-of-Day Z-Report generated.',
        ]);
    }
}
