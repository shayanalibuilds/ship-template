<?php

declare(strict_types=1);

use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the create form', function (): void {
    $this->get(route('releases.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page->component('Releases/Create'));
});

it('validates the create form', function (): void {
    $this->from(route('releases.create'))
        ->post(route('releases.store'), [])
        ->assertRedirect(route('releases.create'))
        ->assertSessionHasErrors(['title', 'body']);
});

it('creates a draft release from the form', function (): void {
    $this->post(route('releases.store'), [
        'title' => 'My First Release',
        'body' => "It works.\n\nOut of the box.",
    ])->assertRedirect(route('releases.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('releases', [
        'title' => 'My First Release',
        'slug' => 'my-first-release',
        'status' => 'draft',
    ]);
});

it('generates a unique slug when the title exists', function (): void {
    Release::factory()->create(['slug' => 'my-first-release']);

    $this->post(route('releases.store'), [
        'title' => 'My First Release',
        'body' => 'Second one with the same title.',
    ])->assertRedirect(route('releases.index'));

    $this->assertDatabaseHas('releases', ['slug' => 'my-first-release-2']);
});
