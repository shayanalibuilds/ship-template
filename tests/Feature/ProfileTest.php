<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page->component('Profile/Edit'),
        );
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('profile.edit', absolute: false));

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com');
});

test('email keeps its verification status when unchanged', function (): void {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Same Email User',
            'email' => $user->email,
        ]);

    $user->refresh();

    expect($user->email_verified_at)->not->toBeNull();
});

test('account can be deleted', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('home', absolute: false));

    $this->assertGuest();
    expect(User::find($user->getKey()))->toBeNull();
});

test('the correct password is required to delete the account', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('password');

    expect(User::whereKey($user->getKey())->exists())->toBeTrue();
});
