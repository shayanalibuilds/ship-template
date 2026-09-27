<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. You
| may extend the Expectation API at any time to add helpers that make your tests
| read like plain English.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

if (! function_exists('ship_sandbox')) {
    /**
     * Copy the rename fixtures into a throwaway directory.
     *
     * Every rename test runs against its own sandbox so parallel test
     * processes never write to the real repository files.
     */
    function ship_sandbox(): string
    {
        $sandbox = sys_get_temp_dir().'/ship-rename-'.uniqid((string) getmypid(), true);

        (new Filesystem)->copyDirectory(
            __DIR__.'/Fixtures/rename',
            $sandbox,
        );

        return $sandbox;
    }
}

if (! function_exists('ship_sandbox_path')) {
    /**
     * Absolute path of a file inside a sandbox, by its repo-relative name.
     */
    function ship_sandbox_path(string $sandbox, string $relative): string
    {
        return $sandbox.'/'.$relative;
    }
}

if (! function_exists('ship_sandbox_read')) {
    /**
     * Contents of a sandbox file, or a marker when the file is absent.
     */
    function ship_sandbox_read(string $sandbox, string $relative): string
    {
        $path = ship_sandbox_path($sandbox, $relative);

        return is_file($path) ? (string) file_get_contents($path) : '[missing:'.$relative.']';
    }
}

if (! function_exists('ship_sandbox_remove')) {
    /**
     * Delete a sandbox and everything inside it.
     */
    function ship_sandbox_remove(string $sandbox): void
    {
        (new Filesystem)->deleteDirectory($sandbox);
    }
}
