<?php

declare(strict_types=1);

use App\Actions\Ship\RenameProject;

beforeEach(function (): void {
    $this->sandbox = ship_sandbox();

    $this->app->singleton(
        RenameProject::class,
        fn (): RenameProject => new RenameProject($this->sandbox),
    );
});

afterEach(function (): void {
    ship_sandbox_remove($this->sandbox);
});

it('renames the project without asking when --force is used', function (): void {
    $this->artisan('ship:rename', ['name' => 'My Product', '--owner' => 'acme', '--force' => true])
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, 'README.md'))
        ->toContain('github.com/acme/my-product')
        ->not->toContain('SHIP_PROJECT_SLUG');

    expect(ship_sandbox_read($this->sandbox, 'composer.json'))
        ->toContain('"name": "acme/my-product"');
});

it('previews every change and writes nothing with --dry-run', function (): void {
    $before = ship_sandbox_read($this->sandbox, 'composer.json');

    $this->artisan('ship:rename', ['name' => 'My Product', '--dry-run' => true])
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, 'composer.json'))->toBe($before);

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))->toContain('SHIP_GITHUB_OWNER');
});

it('applies the rename after a confirmation', function (): void {
    $this->artisan('ship:rename', ['name' => 'My Product', '--owner' => 'acme'])
        ->expectsConfirmation('Apply this rename?', 'yes')
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))
        ->toContain('Copyright (c) 2026 acme');
});

it('changes nothing when the confirmation is declined', function (): void {
    $before = ship_sandbox_read($this->sandbox, 'LICENSE');

    $this->artisan('ship:rename', ['name' => 'My Product', '--owner' => 'acme'])
        ->expectsConfirmation('Apply this rename?', 'no')
        ->assertFailed();

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))->toBe($before);
});

it('rejects a product name without letters or numbers', function (): void {
    $before = ship_sandbox_read($this->sandbox, 'LICENSE');

    $this->artisan('ship:rename', ['name' => '   ', '--force' => true])
        ->assertFailed();

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))->toBe($before);
});
