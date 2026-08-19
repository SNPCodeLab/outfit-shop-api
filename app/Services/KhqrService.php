<?php

declare(strict_types=1);

namespace App\Services;

class KhqrService
{
    /**
     * Compute CRC-16 CCITT (0xFFFF) Checksum for EMVCo Bakong KHQR
     */
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Format a standard EMVCo Tag-Length-Value (TLV) block
     */
    public static function formatTlv(string $tag, string $value): string
    {
        $len = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);

        return $tag.$len.$value;
    }

    /**
     * Generate dynamic Bakong KHQR EMVCo string payload
     */
    public static function generateDynamicKhqr(
        float $amount,
        string $currency = 'USD',
        string $billNumber = 'INV-001',
        string $bakongAccountId = 'khmeriel_pos@bakong',
        string $merchantName = 'KhmeRiel Clothing & MIS',
        string $merchantCity = 'Phnom Penh'
    ): array {
        $currencyCode = strtoupper($currency) === 'KHR' ? '116' : '840';
        $formattedAmount = number_format($amount, 2, '.', '');

        // Merchant Account Information (Tag 29)
        $subTag00 = self::formatTlv('00', 'bakong@nbc');
        $subTag01 = self::formatTlv('01', $bakongAccountId);
        $tag29Value = $subTag00.$subTag01;
        $tag29 = self::formatTlv('29', $tag29Value);

        // Additional Data Field Template (Tag 62)
        $subTag01Bill = self::formatTlv('01', $billNumber);
        $subTag07Terminal = self::formatTlv('07', 'POS-01');
        $tag62Value = $subTag01Bill.$subTag07Terminal;
        $tag62 = self::formatTlv('62', $tag62Value);

        $payloadWithoutCrc =
            self::formatTlv('00', '01').                     // Payload Format Indicator
            self::formatTlv('01', '12').                     // Point of Initiation Method: Dynamic (12)
            $tag29.                                          // Merchant Account Info
            self::formatTlv('52', '5699').                   // Merchant Category Code (Apparel / Retail)
            self::formatTlv('53', $currencyCode).            // Transaction Currency (840 USD / 116 KHR)
            self::formatTlv('54', $formattedAmount).         // Transaction Amount
            self::formatTlv('58', 'KH').                     // Country Code
            self::formatTlv('59', $merchantName).            // Merchant Name
            self::formatTlv('60', $merchantCity).            // Merchant City
            $tag62.                                          // Additional Data (Bill Number)
            '6304';                                           // CRC Tag Header

        $checksum = self::crc16($payloadWithoutCrc);
        $fullQrString = $payloadWithoutCrc.$checksum;

        // Exchange rate calculations
        $khrExchangeRate = 4100;
        $amountUsd = strtoupper($currency) === 'USD' ? $amount : round($amount / $khrExchangeRate, 2);
        $amountKhr = strtoupper($currency) === 'KHR' ? $amount : round($amount * $khrExchangeRate, 0);

        return [
            'qr_string' => $fullQrString,
            'bill_number' => $billNumber,
            'currency' => $currency,
            'amount_usd' => $amountUsd,
            'amount_khr' => $amountKhr,
            'exchange_rate' => $khrExchangeRate,
            'merchant_name' => $merchantName,
            'bakong_account' => $bakongAccountId,
            'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($fullQrString),
        ];
    }
}
