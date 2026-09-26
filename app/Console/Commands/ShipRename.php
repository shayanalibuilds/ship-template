<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Ship\RenamePlan;
use App\Actions\Ship\RenameProject;
use App\Actions\Ship\RenameReport;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Takes ownership of the template by renaming it to your product.
 */
final class ShipRename extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ship:rename
        {name : The product name in quotes, e.g. "Acme Portal"}
        {--owner= : GitHub owner or organisation, used for the composer name}
        {--agent-label= : Label agents use when they commit on your behalf}
        {--dry-run : Preview every change without writing any file}
        {--force : Apply the rename without asking for confirmation}';

    /**
     * @var string
     */
    protected $description = 'Rename the template to your product and fill every SHIP_* token';

    /**
     * Run the rename in two passes: a preview first, then the real write.
     */
    public function handle(RenameProject $rename): int
    {
        $name = trim((string) $this->argument('name'));

        if (Str::slug($name) === '') {
            $this->error('Give the product a name with at least one letter or number.');

            return self::FAILURE;
        }

        $buildPlan = fn (bool $dryRun): RenamePlan => new RenamePlan(
            name: $name,
            owner: $this->ownerOption(),
            agentLabel: $this->agentLabelOption(),
            dryRun: $dryRun,
        );

        $this->preview($rename->handle($buildPlan(true)));

        if ((bool) $this->option('dry-run')) {
            $this->info('Dry run complete. Nothing was written.');

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force') && ! $this->confirm('Apply this rename?')) {
            $this->warn('Rename cancelled. Nothing was written.');

            return self::FAILURE;
        }

        $report = $rename->handle($buildPlan(false));

        $this->info("Renamed to {$name}. Files updated: ".count($report->written).'.');
        $this->newLine();
        $this->line('Run <info>composer install</info> so composer.json is fully in sync.');

        if ($report->untouched !== []) {
            $this->warn('Left as placeholders (no value given): '.implode(', ', $report->untouched));
        }

        return self::SUCCESS;
    }

    /**
     * Print the change table produced by a rename run.
     */
    private function preview(RenameReport $report): void
    {
        $rows = [];

        foreach ($report->written as $file) {
            $rows[] = [$file, 'updated'];
        }

        foreach ($report->missing as $file) {
            $rows[] = [$file, 'not found, skipped'];
        }

        foreach ($report->untouched as $token) {
            $rows[] = [$token, 'kept as placeholder'];
        }

        $this->table(['File', 'Change'], $rows);
    }

    /**
     * The trimmed --owner value, or null when it was not given.
     */
    private function ownerOption(): ?string
    {
        return $this->trimmedOption('owner');
    }

    /**
     * The trimmed --agent-label value, or null when it was not given.
     */
    private function agentLabelOption(): ?string
    {
        return $this->trimmedOption('agent-label');
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
