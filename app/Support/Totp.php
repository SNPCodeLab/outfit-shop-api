<?php

declare(strict_types=1);

namespace App\Support;

/**
 * RFC 6238 TOTP (Time-based One-Time Password) implementation with
 * RFC 4648 Base32 encoding. Compatible with Google Authenticator,
 * Microsoft Authenticator, and 1Password using the otpauth:// URL
 * scheme (SHA-1, 6 digits, 30-second period).
 */
class Totp
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure Base32-encoded secret.
     * 20 raw bytes encodes to 32 Base32 characters (160-bit key).
     */
    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    /**
     * Compute the TOTP code for the given secret at a point in time.
     */
    public static function code(string $base32Secret, ?int $timestamp = null, int $period = 30, int $digits = 6): string
    {
        $counter = intdiv($timestamp ?? time(), $period);

        return self::hotp(self::base32Decode($base32Secret), $counter, $digits);
    }

    /**
     * Verify a submitted 6-digit code against the secret with a
     * clock-skew tolerance window (default +/- 1 step of 30 seconds).
     * Uses hash_equals for constant-time comparison.
     */
    public static function verify(string $base32Secret, string $code, int $window = 1, ?int $timestamp = null, int $period = 30): bool
    {
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timestamp ??= time();
        $counter = intdiv($timestamp, $period);
        $secret = self::base32Decode($base32Secret);

        for ($i = -$window; $i <= $window; $i++) {
            $expected = self::hotp($secret, $counter + $i, 6);
            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * RFC 4226 HOTP: HMAC-SHA1 over the 64-bit big-endian counter.
     */
    private static function hotp(string $key, int $counter, int $digits): string
    {
        $counterBytes = pack('NN', $counter >> 32, $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $counterBytes, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** $digits)), $digits, '0', STR_PAD_LEFT);
    }

    public static function base32Encode(string $data): string
    {
        $output = '';
        $bits = 0;
        $value = 0;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $value = ($value << 8) | ord($data[$i]);
            $bits += 8;

            while ($bits >= 5) {
                $output .= self::BASE32_ALPHABET[($value >> ($bits - 5)) & 31];
                $bits -= 5;
            }
        }

        if ($bits > 0) {
            $output .= self::BASE32_ALPHABET[($value << (5 - $bits)) & 31];
        }

        return $output;
    }

    public static function base32Decode(string $base32): string
    {
        $base32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $base32) ?? '');
        $output = '';
        $bits = 0;
        $value = 0;

        for ($i = 0, $len = strlen($base32); $i < $len; $i++) {
            $pos = strpos(self::BASE32_ALPHABET, $base32[$i]);
            if ($pos === false) {
                continue;
            }

            $value = ($value << 5) | $pos;
            $bits += 5;

            if ($bits >= 8) {
                $output .= chr(($value >> ($bits - 8)) & 255);
                $bits -= 8;
            }
        }

        return $output;
    }
}
