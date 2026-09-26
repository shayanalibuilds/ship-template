<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ship rename contract
    |--------------------------------------------------------------------------
    |
    | The ship:rename command takes ownership of this template by filling
    | SHIP_* tokens and rewriting a few well-known files. Tokens are the
    | rename API: list a file below and any SHIP_* token inside it gets a
    | real value when the product is renamed.
    |
    */

    // Repo-relative files scanned for SHIP_* tokens.
    'token_files' => [
        'LICENSE',
        'README.md',
    ],

    // Repo-relative env files whose APP_NAME line is rewritten.
    'env_files' => [
        '.env.example',
        '.env',
    ],
];
