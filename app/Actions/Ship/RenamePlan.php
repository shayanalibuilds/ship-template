<?php

declare(strict_types=1);

namespace App\Actions\Ship;

use Illuminate\Support\Str;

/**
 * Everything the rename needs, derived once from the command input.
 */
final readonly class RenamePlan
{
    /**
     * The kebab-case project slug, computed from the product name.
     */
    public string $slug;

    public function __construct(
        public string $name,
        public ?string $owner,
        public ?string $agentLabel,
        public bool $dryRun,
    ) {
        $this->slug = Str::slug($this->name);
    }

    /**
     * The token replacements this rename knows about.
     *
     * A null value means "no value given yet", so the token stays as-is.
     *
     * @return array<string, string|null>
     */
    public function tokens(): array
    {
        return [
            'SHIP_APP_NAME' => $this->name,
            'SHIP_PROJECT_SLUG' => $this->slug,
            'SHIP_GITHUB_OWNER' => $this->owner,
            'SHIP_VENDOR_NAMESPACE' => $this->owner,
            'SHIP_AGENT_LABEL' => $this->agentLabel,
        ];
    }

    /**
     * The tokens that have a value and will be replaced.
     *
     * @return array<string, string>
     */
    public function activeTokens(): array
    {
        return array_filter($this->tokens(), is_string(...));
    }

    /**
     * The tokens left in place because no value was given.
     *
     * @return list<string>
     */
    public function untouchedTokens(): array
    {
        return array_keys(array_filter($this->tokens(), is_null(...)));
    }
}
