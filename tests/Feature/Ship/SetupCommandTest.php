<?php

declare(strict_types=1);

use App\Actions\Ship\RenameProject;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

beforeEach(function (): void {
    $this->sandbox = ship_sandbox();

    $this->app->singleton(
        RenameProject::class,
        fn (): RenameProject => new RenameProject($this->sandbox),
    );

    Process::fake();
});

afterEach(function (): void {
    ship_sandbox_remove($this->sandbox);
});

it('sets a sandbox project up end to end when forced', function (): void {
    mkdir($this->sandbox.'/.git', 0777, true);
    file_put_contents($this->sandbox.'/.git/HEAD', 'ref: refs/heads/main');

    $this->artisan('ship:setup', ['--name' => 'My Product', '--owner' => 'acme', '--path' => $this->sandbox, '--force' => true])
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, '.env'))
        ->toContain('APP_KEY=base64:')
        ->toContain('APP_NAME="My Product"');

    expect(ship_sandbox_read($this->sandbox, 'README.md'))
        ->toContain('github.com/acme/my-product');

    Process::assertRan('npm install');
    Process::assertRan('npm run build');
    Process::assertRan('npm run build:ssr');
    Process::assertRan(fn ($process, $result): bool => str_ends_with(implode(' ', (array) $process->command), 'artisan migrate --force'));

    expect(is_dir($this->sandbox.'/.git'))->toBeFalse();
});

it('asks for the product name when none is given', function (): void {
    $this->artisan('ship:setup', ['--path' => $this->sandbox, '--force' => true])
        ->expectsQuestion('What is the product name?', 'Prompted Product')
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, 'composer.json'))
        ->toContain('Prompted Product');
});

it('stops before writing anything when the name is empty', function (): void {
    $before = ship_sandbox_read($this->sandbox, 'composer.json');
    $envBefore = ship_sandbox_read($this->sandbox, '.env');

    $this->artisan('ship:setup', ['--path' => $this->sandbox, '--force' => true])
        ->expectsQuestion('What is the product name?', '   ')
        ->assertFailed();

    expect(ship_sandbox_read($this->sandbox, 'composer.json'))->toBe($before);
    expect(ship_sandbox_read($this->sandbox, '.env'))->toBe($envBefore);
    Process::assertDidntRun('npm install');
});

it('keeps the git history when the deletion is declined', function (): void {
    mkdir($this->sandbox.'/.git', 0777, true);

    $this->artisan('ship:setup', ['--name' => 'My Product', '--path' => $this->sandbox])
        ->expectsConfirmation('Apply this rename?', 'yes')
        ->expectsConfirmation('Delete the template git history? This cannot be undone.', 'no')
        ->assertSuccessful();

    expect(is_dir($this->sandbox.'/.git'))->toBeTrue();
});

it('stops before the git deletion when the build fails', function (): void {
    mkdir($this->sandbox.'/.git', 0777, true);

    Process::fake(fn (PendingProcess $process) => str_starts_with((string) $process->command, 'npm run build') ? Process::result(exitCode: 1) : Process::result());

    $this->artisan('ship:setup', ['--name' => 'My Product', '--path' => $this->sandbox, '--force' => true])
        ->assertFailed();

    expect(is_dir($this->sandbox.'/.git'))->toBeTrue();
});

it('keeps an app key that is already set', function (): void {
    file_put_contents($this->sandbox.'/.env', "APP_NAME=\"Ship Template\"\nAPP_KEY=base64:existing-key\n");

    $this->artisan('ship:setup', ['--name' => 'My Product', '--path' => $this->sandbox, '--force' => true])
        ->assertSuccessful();

    expect(ship_sandbox_read($this->sandbox, '.env'))->toContain('APP_KEY=base64:existing-key');
});
