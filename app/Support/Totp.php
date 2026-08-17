<?php

namespace App\Support;

/**
 * Minimal RFC 6238 TOTP (the algorithm behind Google Authenticator, Authy,
 * 1Password, etc.) implemented from scratch rather than pulling in a
 * composer package — the whole thing is ~80 lines and this keeps the
 * project's dependency count exactly where it was.
 *
 * No QR code is generated (that would need an image/QR library, or worse, a
 * call to a third-party API that would see the secret in plaintext). Users
 * add the account by typing the manual entry key instead — every
 * authenticator app supports this.
 */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD = 30;
    private const DIGITS = 6;

    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    /**
     * otpauth:// URI for apps that support "scan from clipboard" / deep
     * links. The manual key (the raw $secret) is what's shown as the primary
     * setup method.
     */
    public static function otpAuthUri(string $secret, string $accountName, string $issuer): string
    {
        $label = rawurlencode($issuer).':'.rawurlencode($accountName);

        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * The current (or given) time step's 6-digit code. Exposed mainly for
     * testing against known RFC 6238 vectors; verify() is what login uses.
     */
    public static function code(string $base32Secret, ?int $timestamp = null): string
    {
        $timeStep = intdiv($timestamp ?? time(), self::PERIOD);

        return self::codeForStep(self::base32Decode($base32Secret), $timeStep);
    }

    /**
     * Accepts the current time step and one step on either side, so a code
     * typed just before/after a 30-second boundary still works.
     */
    public static function verify(string $base32Secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! is_string($code) || ! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timeStep = intdiv($timestamp ?? time(), self::PERIOD);
        $secretBytes = self::base32Decode($base32Secret);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::codeForStep($secretBytes, $timeStep + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function codeForStep(string $secretBytes, int $timeStep): string
    {
        // 8-byte big-endian counter, per RFC 4226 (HOTP, which TOTP builds on).
        $counter = pack('N*', 0, $timeStep);
        $hash = hash_hmac('sha1', $counter, $secretBytes, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($truncated % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function base32Encode(string $data): string
    {
        $bits = '';
        foreach (str_split($data) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $output .= self::ALPHABET[bindec($chunk)];
        }

        return $output;
    }

    public static function base32Decode(string $data): string
    {
        $data = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $data));

        $bits = '';
        foreach (str_split($data) as $char) {
            $value = strpos(self::ALPHABET, $char);
            if ($value === false) {
                continue;
            }
            $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }

        return $bytes;
    }
}
