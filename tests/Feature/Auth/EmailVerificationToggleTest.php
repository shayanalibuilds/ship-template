<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('registration sends a verification email while the feature is on', function (): void {
    Notification::fake();

    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('unverified users are bounced to the verification notice while the feature is on', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('verification.notice'));
});

test('verified users browse normally while the feature is on', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();
});

test('unverified users browse normally while the feature is off', function (): void {
    config(['features.email_verification' => false]);

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();
});

test('registration stays quiet while the feature is off', function (): void {
    config(['features.email_verification' => false]);

    Notification::fake();

    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Notification::assertNothingSent();
});

test('the verification routes are not found while the feature is off', function (): void {
    config(['features.email_verification' => false]);

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertNotFound();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertNotFound();
});
