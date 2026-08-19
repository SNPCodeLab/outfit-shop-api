<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseApiController
{
    /**
     * GET /api/v1/currencies/rates
     * Return active multi-currency exchange rates and branch baseline.
     */
    public function rates(): JsonResponse
    {
        return $this->successResponse([
            'primary_currency' => 'USD',
            'secondary_currency' => 'KHR',
            'symbol_usd' => '$',
            'symbol_khr' => '៛',
            'exchange_rate' => CurrencyService::DEFAULT_USD_TO_KHR_RATE,
            'rate_source' => 'National Bank of Cambodia (NBC) Benchmark',
            'cash_rounding_rule' => 'Nearest 100 Riels for physical cash drawer',
        ], 'Multi-currency exchange rates retrieved');
    }

    /**
     * POST /api/v1/currencies/convert
     * Convert amount between USD and KHR for POS cash register tendered amounts.
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'nullable|string|in:USD,KHR,usd,khr',
            'to' => 'nullable|string|in:USD,KHR,usd,khr',
            'rate' => 'nullable|numeric|min:1',
        ]);

        $from = strtoupper($validated['from'] ?? 'USD');
        $to = strtoupper($validated['to'] ?? ($from === 'USD' ? 'KHR' : 'USD'));
        $rate = (float) ($validated['rate'] ?? CurrencyService::DEFAULT_USD_TO_KHR_RATE);

        $result = CurrencyService::convert((float) $validated['amount'], $from, $to, $rate);

        return $this->successResponse($result, __('api.currency_converted'));
    }
}
