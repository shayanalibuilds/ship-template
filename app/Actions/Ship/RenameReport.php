<?php

declare(strict_types=1);

namespace App\Actions\Ship;

/**
 * What one rename run changed, so the command can print it.
 */
final readonly class RenameReport
{
    /**
     * @param  list<string>  $written  repo-relative files changed (or that would change)
     * @param  list<string>  $untouched  tokens kept as placeholders because no value was given
     * @param  list<string>  $missing  configured files that do not exist on disk
     */
    public function __construct(
        public array $written,
        public array $untouched,
        public array $missing,
    ) {}
}
