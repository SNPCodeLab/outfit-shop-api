<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\TestCase;

class TotpTest extends TestCase
{
    public function test_generated_secret_is_valid_base32_and_cryptographically_sized(): void
    {
        $secret = Totp::generateSecret();

        $this->assertSame(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_code_round_trips_through_verification(): void
    {
        $secret = Totp::generateSecret();
        $code = Totp::code($secret, time());

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertTrue(Totp::verify($secret, $code));
    }

    public function test_verification_accepts_adjacent_time_step_within_skew_window(): void
    {
        $secret = Totp::generateSecret();
        $now = time();

        $previousStepCode = Totp::code($secret, $now - 30);

        $this->assertTrue(Totp::verify($secret, $previousStepCode, window: 1, timestamp: $now));
        $this->assertFalse(Totp::verify($secret, $previousStepCode, window: 0, timestamp: $now));
    }

    public function test_verification_rejects_wrong_code(): void
    {
        $secret = Totp::generateSecret();
        $code = Totp::code($secret, time());

        $wrong = str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->assertFalse(Totp::verify($secret, $wrong));
    }

    public function test_verification_rejects_malformed_codes(): void
    {
        $secret = Totp::generateSecret();

        $this->assertFalse(Totp::verify($secret, '12345'));
        $this->assertFalse(Totp::verify($secret, '1234567'));
        $this->assertFalse(Totp::verify($secret, 'abcdef'));
    }

    public function test_known_rfc_6238_vector(): void
    {
        // RFC 6238 Appendix B test vector: 20-byte ASCII seed
        // "12345678901234567890" at T=59 (SHA-1, 8 digits) = 94287082.
        $base32Secret = Totp::base32Encode('12345678901234567890');
        $code = Totp::code($base32Secret, 59, period: 30, digits: 8);

        $this->assertSame('94287082', $code);
    }
}
