<?php

declare(strict_types=1);

namespace App\Actions\Ship;

use JsonException;

/**
 * Takes ownership of the template: fills SHIP_* tokens and rewrites the
 * well-known files (composer.json, env files) for the new product.
 */
final readonly class RenameProject
{
    public function __construct(
        private ?string $rootPath = null,
    ) {}

    /**
     * Run the rename and report every file it touched.
     *
     * In a dry run the report is identical, but no file is written.
     */
    public function handle(RenamePlan $plan): RenameReport
    {
        $written = [];
        $missing = [];
        $active = $plan->activeTokens();

        foreach ($this->tokenFiles() as $file) {
            $contents = $this->read($file, $missing);

            if ($contents === null) {
                continue;
            }

            $filled = str_replace(array_keys($active), array_values($active), $contents);

            if ($filled !== $contents) {
                $this->write($file, $filled, $plan);
                $written[] = $file;
            }
        }

        foreach ($this->envFiles() as $file) {
            $contents = $this->read($file, $missing);

            if ($contents === null) {
                continue;
            }

            $filled = $this->replaceAppName($contents, $plan->name);

            if ($filled !== $contents) {
                $this->write($file, $filled, $plan);
                $written[] = $file;
            }
        }

        $contents = $this->read('composer.json', $missing);

        if ($contents !== null) {
            $filled = $this->rewriteComposer($contents, $plan);

            if ($filled !== $contents) {
                $this->write('composer.json', $filled, $plan);
                $written[] = 'composer.json';
            }
        }

        return new RenameReport($written, $plan->untouchedTokens(), $missing);
    }

    /**
     * Contents of a repo-relative file, or null after recording it as missing.
     *
     * @param  list<string>  $missing
     */
    private function read(string $file, array &$missing): ?string
    {
        $path = $this->path($file);

        if (! is_file($path)) {
            $missing[] = $file;

            return null;
        }

        return (string) file_get_contents($path);
    }

    /**
     * Write file contents unless this run is a dry run.
     */
    private function write(string $file, string $contents, RenamePlan $plan): void
    {
        if ($plan->dryRun) {
            return;
        }

        file_put_contents($this->path($file), $contents);
    }

    /**
     * Point the APP_NAME line of an env file at the new product.
     */
    private function replaceAppName(string $contents, string $name): string
    {
        $quoted = '"'.addcslashes($name, '"\\').'"';

        return (string) preg_replace('/^APP_NAME=.*$/m', 'APP_NAME='.$quoted, $contents, 1);
    }

    /**
     * Set the composer package name and description for the new product.
     *
     * @throws JsonException when composer.json is not valid JSON
     */
    private function rewriteComposer(string $contents, RenamePlan $plan): string
    {
        $composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($composer)) {
            throw new JsonException('composer.json must contain a JSON object.');
        }

        if (is_string($plan->owner)) {
            $composer['name'] = $plan->owner.'/'.$plan->slug;
        }

        $composer['description'] = $plan->name.' - a Laravel + Inertia product.';

        $encoded = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new JsonException('composer.json could not be encoded.');
        }

        return $encoded."\n";
    }

    /**
     * @return list<string>
     */
    private function tokenFiles(): array
    {
        return $this->stringList(config('ship.token_files', []));
    }

    /**
     * @return list<string>
     */
    private function envFiles(): array
    {
        return $this->stringList(config('ship.env_files', []));
    }

    /**
     * Keep the string entries of a config array and drop anything else.
     *
     * @return list<string>
     */
    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter($values, is_string(...)));
    }

    /**
     * Project root: the real repository by default, a sandbox path in tests.
     */
    private function path(string $file): string
    {
        return ($this->rootPath ?? base_path()).'/'.$file;
    }
}
