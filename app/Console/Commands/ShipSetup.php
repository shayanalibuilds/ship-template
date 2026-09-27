<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Ship\PrepareEnvironment;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Bootstraps a fresh template clone into a runnable product with its own history.
 */
final class ShipSetup extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ship:setup
        {--name= : Product name for the rename pass}
        {--owner= : GitHub owner or organisation, used for the composer name}
        {--agent-label= : Label agents use when they commit on your behalf}
        {--path= : Project directory to set up, defaults to this project}
        {--force : Apply the rename and the git history deletion without confirmation}';

    /**
     * @var string
     */
    protected $description = 'Rename the product, install, build, migrate and cut the template git history';

    /**
     * Commands that turn the cloned template into a built application.
     *
     * @var list<string>
     */
    private const NPM_STEPS = [
        'npm install --ignore-scripts',
        'npm run build',
        'npm run build:ssr',
    ];

    /**
     * Run the whole setup: env, rename, npm, migrations, git history.
     */
    public function handle(): int
    {
        $path = $this->projectPath();

        if (! is_file($path.'/composer.json')) {
            $this->error('No composer.json in '.$path.'. Point --path at a project directory.');

            return self::FAILURE;
        }

        $name = $this->productName();

        if ($name === null) {
            $this->error('A product name is required. Run ship:setup with --name "Acme Portal".');

            return self::FAILURE;
        }

        try {
            $prepared = (new PrepareEnvironment($path))->handle();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($prepared) {
            $this->line('Environment ready (.env with a fresh APP_KEY).');
        }

        if ($this->call('ship:rename', $this->renameArguments($name)) !== self::SUCCESS) {
            $this->warn('Rename cancelled. Stopped before npm, migrations and git cleanup.');

            return self::FAILURE;
        }

        if (! $this->runNpmSteps($path)) {
            return self::FAILURE;
        }

        if (! $this->runMigrations($path)) {
            return self::FAILURE;
        }

        return $this->cutGitHistory($path);
    }

    /**
     * Install and build the frontend, reporting failure to the caller.
     */
    private function runNpmSteps(string $path): bool
    {
        if (! is_file($path.'/package.json')) {
            $this->line('No package.json found. Skipped the npm steps.');

            return true;
        }

        foreach (self::NPM_STEPS as $step) {
            $this->line('> '.$step);

            $result = Process::path($path)->forever()->run($step, function (string $type, string $buffer): void {
                $this->output->write($buffer);
            });

            if (! $result->successful()) {
                $this->error($step.' failed. Nothing was deleted.');

                return false;
            }
        }

        return true;
    }

    /**
     * Run the project migrations, reporting failure to the caller.
     */
    private function runMigrations(string $path): bool
    {
        $this->line('> artisan migrate --force');

        $result = Process::path($path)->forever()->run([PHP_BINARY, 'artisan', 'migrate', '--force'], function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $result->successful()) {
            $this->error('Migrations failed. Nothing was deleted.');

            return false;
        }

        return true;
    }

    /**
     * Delete the template git history, asking first unless forced.
     */
    private function cutGitHistory(string $path): int
    {
        $gitPath = $path.'/.git';

        if (! is_dir($gitPath)) {
            $this->info('Setup complete. No template git history found.');

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force') && ! $this->confirm('Delete the template git history? This cannot be undone.')) {
            $this->warn('Kept '.$gitPath.'. Remove it manually for a clean start.');

            return self::SUCCESS;
        }

        (new Filesystem)->deleteDirectory($gitPath);

        $this->info('Template git history removed.');
        $this->newLine();
        $this->line('Start your own history:');
        $this->line('  git init');
        $this->line('  git add -A');
        $this->line('  git commit -m "First commit"');

        return self::SUCCESS;
    }

    /**
     * The product name from --name, or from an interactive prompt.
     */
    private function productName(): ?string
    {
        $name = $this->trimmedOption('name');

        if ($name !== null) {
            return $name;
        }

        $asked = $this->ask('What is the product name?');

        if (! is_string($asked) || trim($asked) === '') {
            return null;
        }

        return trim($asked);
    }

    /**
     * Arguments for the rename pass, dropping unset options.
     *
     * @return array<string, mixed>
     */
    private function renameArguments(string $name): array
    {
        $arguments = ['name' => $name];

        foreach (['owner' => '--owner', 'agent-label' => '--agent-label'] as $option => $flag) {
            $value = $this->trimmedOption($option);

            if ($value !== null) {
                $arguments[$flag] = $value;
            }
        }

        if ((bool) $this->option('force')) {
            $arguments['--force'] = true;
        }

        return $arguments;
    }

    /**
     * The directory to set up: --path when given, otherwise this project.
     */
    private function projectPath(): string
    {
        return $this->trimmedOption('path') ?? base_path();
    }

    /**
     * Read a string option and treat an empty value as "not given".
     */
    private function trimmedOption(string $key): ?string
    {
        $value = $this->option($key);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
