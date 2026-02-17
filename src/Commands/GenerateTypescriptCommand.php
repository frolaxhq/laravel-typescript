<?php

declare(strict_types=1);

namespace Frolax\Typescript\Commands;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Pipeline\GenerationPipeline;
use Frolax\Typescript\Support\FileWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GenerateTypescriptCommand extends Command
{
    protected $signature = 'typescript:generate
        {model? : Generate for a specific model only}
        {--output= : Output path (overrides config)}
        {--writer= : Writer to use: interface, type, json}
        {--enum-style= : Enum style: const_object, ts_enum, union}
        {--global : Wrap output in declare global namespace}
        {--plurals : Pluralize type names}
        {--api-resources : Generate API resource types}
        {--fillables : Generate fillable types}
        {--fillable-suffix=Fillable : Suffix for fillable types}
        {--no-relations : Exclude relations}
        {--optional-relations : Make relations optional}
        {--no-counts : Exclude relation counts}
        {--optional-counts : Make counts optional}
        {--no-exists : Exclude relation exists}
        {--optional-exists : Make exists optional}
        {--no-sums : Exclude sums}
        {--optional-sums : Make sums optional}
        {--no-hidden : Exclude hidden columns}
        {--timestamps-as-date : Map timestamps to Date instead of string}
        {--optional-nullables : Make nullable columns optional}
        {--connection= : Database connection to use}
        {--strict : Bail on any model processing error}
        {--no-format : Skip formatting}
        {--stdout : Output to stdout only (no files written)}';

    protected $description = 'Generate TypeScript interfaces from Eloquent models';

    public function handle(GenerationPipeline $pipeline): int
    {
        $this->info('Generating TypeScript definitions...');

        $config = GenerationConfig::fromArray(
            config: Config::get('typescript', []),
            options: $this->buildOptions(),
        );

        try {
            $result = $pipeline->generate($config);

            // Output warnings
            foreach ($result->warnings as $warning) {
                $this->warn("⚠ {$warning}");
            }

            $output = $result->output;

            // Handle output
            if ($this->option('stdout')) {
                // Output to stdout
                if ($output?->stdout) {
                    $this->line($output->stdout);
                }
            } else {
                // Write files to disk
                $outputPath = $this->option('output') ?? $config->outputPath;
                $fileWriter = new FileWriter;

                if (! empty($output?->files)) {
                    // Per-model or multi-file mode
                    $fileWriter->cleanDirectory($outputPath);
                    $fileWriter->writeFiles($output->files, $outputPath);
                    $count = count($output->files);
                    $this->info("   Wrote {$count} file(s) to: {$outputPath}");
                } elseif ($output?->stdout) {
                    // Single bundled file mode
                    $singleFileName = $config->singleFileName ?? 'models.d.ts';
                    $fullPath = rtrim($outputPath, '/').'/'.$singleFileName;
                    $fileWriter->writeSingleFile($fullPath, $output->stdout);
                    $this->info("   Wrote: {$fullPath}");
                }
            }

            // Summary
            $modelCount = $result->models->count();
            $enumCount = $result->enums->count();

            $this->info("✅ Generated {$modelCount} model(s) and {$enumCount} enum(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Build CLI options array from command arguments/options.
     *
     * @return array<string, mixed>
     */
    private function buildOptions(): array
    {
        $options = array_filter([
            'model' => $this->argument('model'),
            'output' => $this->option('output'),
            'writer' => $this->option('writer'),
            'enum-style' => $this->option('enum-style'),
            'fillable-suffix' => $this->option('fillable-suffix'),
            'connection' => $this->option('connection'),
        ], fn ($v) => $v !== null);

        // Boolean flags (only set if explicitly passed)
        $booleanFlags = [
            'global', 'plurals', 'api-resources', 'fillables',
            'no-relations', 'optional-relations',
            'no-counts', 'optional-counts',
            'no-exists', 'optional-exists',
            'no-sums', 'optional-sums',
            'no-hidden', 'timestamps-as-date', 'optional-nullables',
            'strict', 'no-format',
        ];

        foreach ($booleanFlags as $flag) {
            if ($this->option($flag)) {
                $options[$flag] = true;
            }
        }

        return $options;
    }
}
