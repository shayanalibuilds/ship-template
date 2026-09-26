<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withSkip([
        // Runtime-generated files. Never rewrite them.
        __DIR__.'/bootstrap/cache',
        // Laravel requires seeder run() to exist, even when nothing is seeded.
        RemoveEmptyClassMethodRector::class => [__DIR__.'/database/seeders'],
    ]);
