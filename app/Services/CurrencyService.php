<?php

namespace App\Services;

class CurrencyService
{
    // National Bank of Cambodia (NBC) standard baseline rate
    public const DEFAULT_USD_TO_KHR_RATE = 4100.00;

    /**
     * Convert an amount between USD and KHR.
     */
    public static function convert(float $amount, string $from = 'USD', string $to = 'KHR', float $rate = self::DEFAULT_USD_TO_KHR_RATE): array
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return [
                'from_amount'   => $amount,
                'from_currency' => $from,
                'to_amount'     => $amount,
                'to_currency'   => $to,
                'exchange_rate' => 1.0,
            ];
        }

        if ($from === 'USD' && $to === 'KHR') {
            // Round to nearest 100 Riels (standard Cambodian cash rounding)
            $converted = round(($amount * $rate) / 100) * 100;
            return [
                'from_amount'   => $amount,
                'from_currency' => 'USD',
                'to_amount'     => $converted,
                'to_currency'   => 'KHR',
                'exchange_rate' => $rate,
                'formatted_khr' => number_format($converted) . ' ៛',
            ];
        }

        if ($from === 'KHR' && $to === 'USD') {
            $converted = round($amount / $rate, 2);
            return [
                'from_amount'   => $amount,
                'from_currency' => 'KHR',
                'to_amount'     => $converted,
                'to_currency'   => 'USD',
                'exchange_rate' => round(1 / $rate, 6),
                'formatted_usd' => '$' . number_format($converted, 2),
            ];
        }

        return [
            'from_amount'   => $amount,
            'from_currency' => $from,
            'to_amount'     => $amount,
            'to_currency'   => $to,
            'exchange_rate' => 1.0,
        ];
    }
}
