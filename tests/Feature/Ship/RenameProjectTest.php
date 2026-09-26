<?php

declare(strict_types=1);

use App\Actions\Ship\RenamePlan;
use App\Actions\Ship\RenameProject;

beforeEach(function (): void {
    $this->sandbox = ship_sandbox();
    $this->rename = new RenameProject($this->sandbox);
});

afterEach(function (): void {
    ship_sandbox_remove($this->sandbox);
});

it('fills ship tokens with the product values', function (): void {
    $report = $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: 'Acme Bot',
        dryRun: false,
    ));

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))
        ->toContain('Copyright (c) 2026 acme')
        ->not->toContain('SHIP_');

    expect(ship_sandbox_read($this->sandbox, 'README.md'))
        ->toContain('https://github.com/acme/acme-portal')
        ->not->toContain('SHIP_');

    expect($report->written)
        ->toContain('LICENSE')
        ->toContain('README.md')
        ->toContain('composer.json')
        ->toContain('.env.example')
        ->toContain('.env')
        ->toHaveCount(5);
});

it('leaves tokens in place when no value is given', function (): void {
    $report = $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: null,
        agentLabel: null,
        dryRun: false,
    ));

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))->toContain('SHIP_GITHUB_OWNER');

    expect(ship_sandbox_read($this->sandbox, 'README.md'))
        ->toContain('SHIP_GITHUB_OWNER')
        ->toContain('acme-portal');

    expect($report->untouched)
        ->toContain('SHIP_GITHUB_OWNER')
        ->toContain('SHIP_VENDOR_NAMESPACE')
        ->toContain('SHIP_AGENT_LABEL');

    expect(json_decode(ship_sandbox_read($this->sandbox, 'composer.json'), true, 512, JSON_THROW_ON_ERROR))
        ->name->toBe('ship-template/ship-template');
});

it('writes nothing during a dry run but still reports the changes', function (): void {
    $before = ship_sandbox_read($this->sandbox, 'LICENSE');

    $report = $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: null,
        dryRun: true,
    ));

    expect($report->written)->toContain('LICENSE');

    expect(ship_sandbox_read($this->sandbox, 'LICENSE'))->toBe($before);
});

it('reports configured files that are missing', function (): void {
    unlink(ship_sandbox_path($this->sandbox, '.env'));

    $report = $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: null,
        dryRun: false,
    ));

    expect($report->missing)->toBe(['.env']);
});

it('rewrites the composer package name and description', function (): void {
    $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: null,
        dryRun: false,
    ));

    expect(json_decode(ship_sandbox_read($this->sandbox, 'composer.json'), true, 512, JSON_THROW_ON_ERROR))
        ->name->toBe('acme/acme-portal')
        ->description->toBe('Acme Portal - a Laravel + Inertia product.');
});

it('keeps the App autoload namespace intact', function (): void {
    $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: null,
        dryRun: false,
    ));

    $composer = json_decode(ship_sandbox_read($this->sandbox, 'composer.json'), true, 512, JSON_THROW_ON_ERROR);

    if (! is_array($composer)) {
        throw new JsonException('composer.json fixture is not a JSON object.');
    }

    expect($composer['autoload']['psr-4'])->toHaveKey('App\\', 'app/');
});

it('rewrites APP_NAME in the env files', function (): void {
    $this->rename->handle(new RenamePlan(
        name: 'Acme Portal',
        owner: 'acme',
        agentLabel: null,
        dryRun: false,
    ));

    expect(ship_sandbox_read($this->sandbox, '.env.example'))->toContain('APP_NAME="Acme Portal"');

    expect(ship_sandbox_read($this->sandbox, '.env'))->toContain('APP_NAME="Acme Portal"');
});
