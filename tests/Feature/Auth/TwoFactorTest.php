<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\TwoFactor\Totp;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * An authenticated user whose password confirmation is still fresh.
 */
function asConfirmed(User $user): TestCase
{
    return test()->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
}

function validTwoFactorCode(User $user): string
{
    return (new Totp)->code((string) $user->two_factor_secret);
}

test('the security page requires a recent password confirmation', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('security.show'))
        ->assertRedirect(route('password.confirm'));
});

test('the security page renders after the password is confirmed', function (): void {
    $user = User::factory()->create();

    asConfirmed($user)
        ->get(route('security.show'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Security/Index')
                ->where('enabled', false)
                ->where('confirming', false)
                ->where('secret', null)
                ->where('qrSvg', null),
        );
});

test('starting the setup stores an unconfirmed secret', function (): void {
    $user = User::factory()->create();

    asConfirmed($user)->post(route('two-factor.store'))->assertRedirect();

    $user = $user->fresh();

    expect($user->hasPendingTwoFactor())->toBeTrue()
        ->and($user->hasTwoFactorEnabled())->toBeFalse();
});

test('the pending setup exposes the secret and the QR code', function (): void {
    $user = User::factory()->create();

    asConfirmed($user)->post(route('two-factor.store'));

    asConfirmed($user->fresh())
        ->get(route('security.show'))
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Security/Index')
                ->where('confirming', true)
                ->whereType('secret', 'string')
                ->whereType('qrSvg', 'string'),
        );
});

test('the setup confirms with a valid code and issues recovery codes', function (): void {
    $user = User::factory()->create();

    asConfirmed($user)->post(route('two-factor.store'));

    $user = $user->fresh();

    asConfirmed($user)
        ->post(route('two-factor.confirm'), [
            'code' => validTwoFactorCode($user),
        ])
        ->assertRedirect();

    $user = $user->fresh();

    expect($user->hasTwoFactorEnabled())->toBeTrue()
        ->and(session('two-factor-codes'))->toHaveCount(10);
});

test('the setup rejects an invalid code', function (): void {
    $user = User::factory()->create();

    asConfirmed($user)->post(route('two-factor.store'));

    $user = $user->fresh();

    asConfirmed($user)
        ->from(route('security.show'))
        ->post(route('two-factor.confirm'), [
            'code' => '000000',
        ])
        ->assertRedirect(route('security.show'))
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('recovery codes can be regenerated', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    asConfirmed($user)
        ->post(route('two-factor.recovery-codes'))
        ->assertRedirect();

    expect(session('two-factor-codes'))->toHaveCount(10);
});

test('two-factor can be switched off again', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    asConfirmed($user)
        ->delete(route('two-factor.destroy'))
        ->assertRedirect();

    $user = $user->fresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and($user->hasTwoFactorEnabled())->toBeFalse();
});

test('users with confirmed two-factor are sent to the challenge', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.challenge'));

    expect(auth()->check())->toBeFalse()
        ->and(session('login.id'))->toBe($user->getKey());
});

test('the challenge completes the login with a valid code', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->get(route('two-factor.challenge'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/TwoFactorChallenge'),
        );

    $response = $this->post(route('two-factor.challenge.store'), [
        'code' => validTwoFactorCode($user->fresh()),
    ]);

    $response->assertRedirect(route('profile.edit', absolute: false));

    $this->assertAuthenticatedAs($user);

    expect(session('login.id'))->toBeNull();
});

test('the challenge rejects an invalid code', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->post(route('two-factor.challenge.store'), [
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('a recovery code works exactly once', function (): void {
    $user = User::factory()->withTwoFactor()->create();
    $codes = $user->generateRecoveryCodes();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.challenge.store'), [
        'code' => $codes[0],
    ])->assertRedirect(route('profile.edit', absolute: false));

    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.challenge.store'), [
        'code' => $codes[0],
    ])->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('the challenge is throttled', function (): void {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = null;

    foreach (range(0, 5) as $attempt) {
        $response = $this->post(route('two-factor.challenge.store'), [
            'code' => '000000',
        ]);
    }

    $response->assertSessionHasErrors('code');

    $errors = session('errors')->get('code');

    expect($errors[0])->toContain('Too many');
});

test('guests without a pending login are sent back to the login screen', function (): void {
    $this->get(route('two-factor.challenge'))
        ->assertRedirect(route('login'));

    $this->post(route('two-factor.challenge.store'), [
        'code' => '123456',
    ])->assertRedirect(route('login'));
});

test('the challenge is not found while the feature is off', function (): void {
    config(['features.two_factor' => false]);

    $this->get(route('two-factor.challenge'))
        ->assertNotFound();
});

test('the login skips the challenge while the feature is off', function (): void {
    config(['features.two_factor' => false]);

    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('the security routes are not found while the feature is off', function (): void {
    config(['features.two_factor' => false]);

    $user = User::factory()->create();

    asConfirmed($user)->get(route('security.show'))->assertNotFound();

    asConfirmed($user)->post(route('two-factor.store'))->assertNotFound();
});
