<?php

declare(strict_types=1);

use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows a published release by slug', function (): void {
    $release = Release::factory()->published()->create(['title' => 'V1 Is Out']);

    $this->get(route('releases.show', $release))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->component('Releases/Show')
            ->where('release.title', 'V1 Is Out')
            ->where('release.slug', $release->slug));
});

it('does not show draft releases to guests', function (): void {
    $release = Release::factory()->create();

    $this->get(route('releases.show', $release))->assertNotFound();
});
