<?php

declare(strict_types=1);

namespace App\Services\TwoFactor;

use RuntimeException;

/**
 * A dependency-free RFC 6238 time-based one-time password generator.
 */
final class Totp
{
    private const int PERIOD = 30;

    private const int DIGITS = 6;

    private const string ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random base32 encoded secret (160 bits).
     */
    public function generateSecret(): string
    {
        return $this->encodeBase32(random_bytes(20));
    }

    /**
     * The six digit code for a secret at a unix timestamp.
     */
    public function code(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $key = $this->decodeBase32($secret);

        if ($key === '') {
            throw new RuntimeException('The two-factor secret is not valid base32.');
        }

        $counter = intdiv($timestamp, self::PERIOD);
        $binary = pack('N2', $counter >> 32, $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $binary, $key, true);

        $offset = ord($hash[19]) & 0x0F;
        $value = (ord($hash[$offset]) & 0x7F) << 24
            | ord($hash[$offset + 1]) << 16
            | ord($hash[$offset + 2]) << 8
            | ord($hash[$offset + 3]);

        return str_pad((string) ($value % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Constant-time verification with a ± window of periods.
     */
    public function verify(string $secret, string $code, ?int $timestamp = null, int $window = 1): bool
    {
        $timestamp ??= time();

        if (preg_match('/^\d{'.self::DIGITS.'}$/', $code) !== 1) {
            return false;
        }

        foreach (range(-$window, $window) as $drift) {
            if (hash_equals($this->code($secret, $timestamp + $drift * self::PERIOD), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The otpauth URI authenticator apps photograph.
     */
    public function otpauthUri(string $issuer, string $label, string $secret): string
    {
        return sprintf(
            'otpauth://totp/%s?issuer=%s&secret=%s',
            rawurlencode($label),
            rawurlencode($issuer),
            $secret,
        );
    }

    /**
     * RFC 4648 base32 encoding without padding.
     */
    private function encodeBase32(string $bytes): string
    {
        $output = '';
        $bits = 0;
        $buffer = 0;

        foreach (str_split($bytes) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $output .= self::ALPHABET[($buffer >> $bits) & 0x1F];
            }
        }

        if ($bits > 0) {
            $output .= self::ALPHABET[($buffer << (5 - $bits)) & 0x1F];
        }

        return $output;
    }

    /**
     * RFC 4648 base32 decoding that tolerates missing padding and casing.
     */
    private function decodeBase32(string $secret): string
    {
        $clean = preg_replace('/[^A-Za-z2-7]/', '', $secret) ?? '';

        if ($clean === '') {
            return '';
        }

        $bits = 0;
        $buffer = 0;
        $output = '';

        foreach (str_split(strtoupper($clean)) as $character) {
            $position = strpos(self::ALPHABET, $character);

            if ($position === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $position;
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }
}
