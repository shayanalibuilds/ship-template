<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('confirm password screen can be rendered', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('password.confirm'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/ConfirmPassword'),
        );
});

test('password can be confirmed', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('password.confirm'), [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('password is not confirmed with an invalid password', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('password.confirm'), [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('password');
});
