<?php

declare(strict_types=1);

use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('lists only published releases', function (): void {
    Release::factory()->count(3)->create();
    Release::factory()->published()->create(['title' => 'The Published One']);

    $this->get(route('releases.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->component('Releases/Index')
            ->has('releases.data', 1)
            ->where('releases.data.0.title', 'The Published One')
            ->where('releases.data.0.status', 'published'));
});

it('paginates the releases index', function (): void {
    Release::factory()->published()->count(11)->create();

    $this->get(route('releases.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page->has('releases.data', 10));
});

it('orders releases newest first', function (): void {
    Release::factory()->published()->create(['title' => 'Older', 'created_at' => now()->subDay()]);
    Release::factory()->published()->create(['title' => 'Newer']);

    $this->get(route('releases.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('releases.data.0.title', 'Newer')
            ->where('releases.data.1.title', 'Older'));
});
