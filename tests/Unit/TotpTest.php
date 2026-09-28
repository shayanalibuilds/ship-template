<?php

declare(strict_types=1);

use App\Services\TwoFactor\Totp;

test('the codes match the RFC 6238 test vectors', function (int $timestamp, string $expected): void {
    // The RFC secret "12345678901234567890" encoded as base32.
    $totp = new Totp;

    expect($totp->code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', $timestamp))->toBe($expected);
})->with([
    [59, '287082'],
    [1111111109, '081804'],
    [1111111111, '050471'],
    [1234567890, '005924'],
    [2000000000, '279037'],
    [20000000000, '353130'],
]);

test('codes are accepted within one period of drift', function (): void {
    $totp = new Totp;
    $secret = $totp->generateSecret();
    $code = $totp->code($secret, 1_700_000_000);

    expect($totp->verify($secret, $code, 1_700_000_000))->toBeTrue()
        ->and($totp->verify($secret, $code, 1_700_000_000 + 30))->toBeTrue()
        ->and($totp->verify($secret, $code, 1_700_000_000 - 30))->toBeTrue();
});

test('codes are rejected outside the drift window', function (): void {
    $totp = new Totp;
    $secret = $totp->generateSecret();
    $code = $totp->code($secret, 1_700_000_000);

    expect($totp->verify($secret, $code, 1_700_000_000 + 120))->toBeFalse()
        ->and($totp->verify($secret, $code, 1_700_000_000 - 120))->toBeFalse();
});

test('malformed codes are rejected', function (): void {
    $totp = new Totp;
    $secret = $totp->generateSecret();

    expect($totp->verify($secret, 'abc123'))->toBeFalse()
        ->and($totp->verify($secret, '12345'))->toBeFalse()
        ->and($totp->verify($secret, '1234567'))->toBeFalse()
        ->and($totp->verify($secret, ''))->toBeFalse();
});

test('secrets are generated as 32 character base32 strings', function (): void {
    $totp = new Totp;
    $secret = $totp->generateSecret();

    expect($secret)->toMatch('/^[A-Z2-7]{32}$/')
        ->and($totp->verify($secret, $totp->code($secret)))->toBeTrue();
});

test('the otpauth uri carries issuer, label and secret', function (): void {
    $totp = new Totp;

    expect($totp->otpauthUri('My Product', 'ada@example.com', 'GEZDGNBVGY3TQOJQ'))
        ->toBe('otpauth://totp/ada%40example.com?issuer=My%20Product&secret=GEZDGNBVGY3TQOJQ');
});
