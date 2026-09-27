<?php

declare(strict_types=1);

namespace App\Actions\Ship;

use RuntimeException;

/**
 * Makes a project ready to run: creates .env from .env.example and fills an empty APP_KEY.
 */
final readonly class PrepareEnvironment
{
    public function __construct(
        private ?string $rootPath = null,
    ) {}

    /**
     * Prepare the env files and report whether anything was written.
     *
     * @throws RuntimeException when neither .env nor .env.example exists
     */
    public function handle(): bool
    {
        $changed = false;

        if (! is_file($this->path('.env'))) {
            $example = $this->path('.env.example');

            if (! is_file($example)) {
                throw new RuntimeException('No .env or .env.example found in '.$this->root());
            }

            file_put_contents($this->path('.env'), (string) file_get_contents($example));
            $changed = true;
        }

        $contents = (string) file_get_contents($this->path('.env'));
        $filled = $this->withAppKey($contents);

        if ($filled !== $contents) {
            file_put_contents($this->path('.env'), $filled);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Replace an empty APP_KEY line with a fresh key, appending one when missing.
     */
    private function withAppKey(string $contents): string
    {
        $filled = (string) preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY='.$this->freshKey(), $contents, 1, $count);

        if ($count === 0 && ! $this->hasKey($contents)) {
            $glue = $contents === '' || str_ends_with($contents, "\n") ? '' : "\n";

            $filled = $contents.$glue.'APP_KEY='.$this->freshKey()."\n";
        }

        return $filled;
    }

    /**
     * Whether the env contents carry a non-empty APP_KEY value.
     */
    private function hasKey(string $contents): bool
    {
        return (bool) preg_match('/^APP_KEY=\S/m', $contents);
    }

    /**
     * A fresh Laravel-format encryption key.
     */
    private function freshKey(): string
    {
        return 'base64:'.base64_encode(random_bytes(32));
    }

    /**
     * Project root: the real repository by default, a sandbox path in tests.
     */
    private function path(string $file): string
    {
        return ($this->rootPath ?? base_path()).'/'.$file;
    }

    /**
     * The root path without a trailing file part, for error messages.
     */
    private function root(): string
    {
        return $this->rootPath ?? base_path();
    }
}
