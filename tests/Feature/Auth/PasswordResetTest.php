<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;

test('forgot password screen can be rendered', function (): void {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/ForgotPassword'),
        );
});

test('reset link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification): bool {
        $this->get(route('password.reset', $notification->token))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/ResetPassword')->has('token'),
            );

        return true;
    });
});

test('password can be reset with a valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    $token = null;

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    $response = $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('login', absolute: false));

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

test('password can not be reset with an invalid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    Password::createToken($user);

    $response = $this->post(route('password.store'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
