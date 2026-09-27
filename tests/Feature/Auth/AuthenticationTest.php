<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('login screen can be rendered', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/Login'),
        );
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('profile.edit', absolute: false));
});

test('users can not authenticate with an invalid password', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('authenticated users are redirected away from the login screen', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('profile.edit', absolute: false));
});

test('users can log out', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home', absolute: false));
});

test('login attempts are throttled', function (): void {
    $user = User::factory()->create();

    $response = null;

    foreach (range(0, 5) as $attempt) {
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $response->assertSessionHasErrors('email');

    $errors = session('errors')->get('email');

    expect($errors[0])->toContain('Too many');
});
