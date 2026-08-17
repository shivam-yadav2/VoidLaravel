<?php

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TotpTest extends TestCase
{
    /**
     * Official RFC 6238 Appendix B SHA1 test vectors. The RFC's ASCII seed
     * "12345678901234567890" is base32-encoded here since Totp::code()
     * takes a base32 secret (what a real authenticator app stores) — that
     * round-trip through base32Encode/Decode is itself being verified, not
     * just the HMAC math.
     *
     * The RFC vectors are 8-digit codes; this implementation produces 6
     * digits (what Google Authenticator / Authy actually display), which is
     * the same truncated value mod 10^6 instead of mod 10^8 — so the
     * expected values here are the last 6 digits of the RFC's published codes.
     */
    public static function vectors(): array
    {
        return [
            'T=59' => [59, '287082'],
            'T=1111111109' => [1111111109, '081804'],
            'T=1111111111' => [1111111111, '050471'],
            'T=1234567890' => [1234567890, '005924'],
            'T=2000000000' => [2000000000, '279037'],
        ];
    }

    #[DataProvider('vectors')]
    public function test_matches_official_rfc6238_test_vectors(int $timestamp, string $expected): void
    {
        $secret = Totp::base32Encode('12345678901234567890');

        $this->assertSame($expected, Totp::code($secret, $timestamp));
    }

    public function test_verify_accepts_the_correct_code(): void
    {
        $secret = Totp::generateSecret();
        $code = Totp::code($secret, 1_700_000_000);

        $this->assertTrue(Totp::verify($secret, $code, window: 1, timestamp: 1_700_000_000));
    }

    public function test_verify_accepts_a_code_from_one_step_earlier_within_window(): void
    {
        $secret = Totp::generateSecret();
        // 1_700_000_010 falls exactly on a 30s step boundary (1_700_000_010 / 30
        // is an integer), so +30 below is guaranteed to be exactly the next step.
        $codeAtT0 = Totp::code($secret, 1_700_000_010);

        $this->assertTrue(Totp::verify($secret, $codeAtT0, window: 1, timestamp: 1_700_000_010 + 30));
    }

    public function test_verify_rejects_a_code_outside_the_window(): void
    {
        $secret = Totp::generateSecret();
        $codeAtT0 = Totp::code($secret, 1_700_000_010);

        // 3 steps away — outside a window of 1.
        $this->assertFalse(Totp::verify($secret, $codeAtT0, window: 1, timestamp: 1_700_000_010 + 90));
    }

    public function test_verify_rejects_wrong_code(): void
    {
        $secret = Totp::generateSecret();

        $this->assertFalse(Totp::verify($secret, '000000', timestamp: 1_700_000_000));
    }

    public function test_verify_rejects_malformed_input(): void
    {
        $secret = Totp::generateSecret();

        $this->assertFalse(Totp::verify($secret, 'abcdef'));
        $this->assertFalse(Totp::verify($secret, '12345'));
        $this->assertFalse(Totp::verify($secret, ''));
    }

    public function test_a_different_secret_produces_a_different_code(): void
    {
        $codeA = Totp::code(Totp::generateSecret(), 1_700_000_000);
        $codeB = Totp::code(Totp::generateSecret(), 1_700_000_000);

        $this->assertNotSame($codeA, $codeB);
    }

    public function test_base32_round_trip(): void
    {
        $original = random_bytes(20);

        $this->assertSame($original, Totp::base32Decode(Totp::base32Encode($original)));
    }
}
