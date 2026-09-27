<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

test('registration screen can be rendered', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/Register'),
        );
});

test('new users can register', function (): void {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('profile.edit', absolute: false));

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull()
        ->and(Hash::check('password', $user->password))->toBeTrue();
});
