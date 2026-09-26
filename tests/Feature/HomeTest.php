<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

test('home page renders', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page->component('Home'));
});
