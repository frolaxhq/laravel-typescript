<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Configuration object for the generation pipeline, built from config + CLI flags.
 */
final readonly class GenerationConfig
{
    /**
     * @param list<string> $paths
     * @param list<string> $additionalPaths
     * @param list<string> $includedModels
     * @param list<string> $excludedModels
     * @param array<string, string> $customMappings
     */
    public function __construct(
        // Discovery
        public array $paths = [],
        public array $additionalPaths = [],
        public array $includedModels = [],
        public array $excludedModels = [],
        public ?string $specificModel = null,

        // Output
        public string $outputPath = '',
        public bool $perModelFiles = true,
        public string $singleFileName = 'models.d.ts',
        public string $enumDirectory = 'enums',
        public bool $barrelExport = true,

        // Writer
        public string $writer = 'interface',
        public string $enumStyle = 'const_object',
        public ?string $globalNamespace = null,
        public bool $plurals = false,
        public bool $apiResources = false,
        public bool $fillableTypes = false,
        public string $fillableSuffix = 'Fillable',

        // Relations
        public bool $relationsEnabled = true,
        public bool $optionalRelations = false,
        public int $maxRelationDepth = 1,
        public bool $countsEnabled = true,
        public bool $optionalCounts = false,
        public bool $existsEnabled = true,
        public bool $optionalExists = false,
        public bool $sumsEnabled = true,
        public bool $optionalSums = false,

        // Mappings
        public array $customMappings = [],
        public bool $timestampsAsDate = false,
        public bool $decimalsAsString = false,

        // Casing
        public string $columnCase = 'snake',
        public string $relationCase = 'snake',

        // Visibility
        public bool $includeHidden = true,
        public bool $optionalNullables = false,

        // Introspection
        public ?string $connection = null,

        // Pipeline
        public bool $bailOnError = false,
        public bool $incremental = false,

        // Formatter
        public bool $formatterEnabled = false,

        // Strict mode
        public bool $strict = false,
    ) {}

    /**
     * Create a GenerationConfig from the application config and CLI options.
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed> $options CLI options override
     */
    public static function fromArray(array $config, array $options = []): self
    {
        return new self(
            paths: $options['paths'] ?? $config['discovery']['paths'] ?? [app_path('Models')],
            additionalPaths: $config['discovery']['additional_paths'] ?? [],
            includedModels: $config['discovery']['included_models'] ?? [],
            excludedModels: $config['discovery']['excluded_models'] ?? [],
            specificModel: $options['model'] ?? null,
            outputPath: $options['output'] ?? $config['output']['path'] ?? resource_path('types/generated'),
            perModelFiles: $config['output']['per_model_files'] ?? true,
            singleFileName: $config['output']['single_file_name'] ?? 'models.d.ts',
            enumDirectory: $config['output']['enum_directory'] ?? 'enums',
            barrelExport: $config['output']['barrel_export'] ?? true,
            writer: $options['writer'] ?? $config['writer']['default'] ?? 'interface',
            enumStyle: $options['enum-style'] ?? $config['writer']['enum_style'] ?? 'const_object',
            globalNamespace: isset($options['global']) && $options['global'] ? ($config['writer']['global_namespace'] ?? 'models') : ($config['writer']['global_namespace'] ?? null),
            plurals: $options['plurals'] ?? $config['writer']['plurals'] ?? false,
            apiResources: $options['api-resources'] ?? $config['writer']['api_resources'] ?? false,
            fillableTypes: $options['fillables'] ?? $config['writer']['fillable_types'] ?? false,
            fillableSuffix: $options['fillable-suffix'] ?? $config['writer']['fillable_suffix'] ?? 'Fillable',
            relationsEnabled: ! ($options['no-relations'] ?? ! ($config['relations']['enabled'] ?? true)),
            optionalRelations: $options['optional-relations'] ?? $config['relations']['optional'] ?? false,
            maxRelationDepth: $config['relations']['max_depth'] ?? 1,
            countsEnabled: ! ($options['no-counts'] ?? ! ($config['relations']['counts']['enabled'] ?? true)),
            optionalCounts: $options['optional-counts'] ?? $config['relations']['counts']['optional'] ?? false,
            existsEnabled: ! ($options['no-exists'] ?? ! ($config['relations']['exists']['enabled'] ?? true)),
            optionalExists: $options['optional-exists'] ?? $config['relations']['exists']['optional'] ?? false,
            sumsEnabled: ! ($options['no-sums'] ?? ! ($config['relations']['sums']['enabled'] ?? true)),
            optionalSums: $options['optional-sums'] ?? $config['relations']['sums']['optional'] ?? false,
            customMappings: $config['mappings']['custom'] ?? [],
            timestampsAsDate: $options['timestamps-as-date'] ?? $config['mappings']['timestamps_as_date'] ?? false,
            decimalsAsString: $config['mappings']['decimals_as_string'] ?? false,
            columnCase: $config['case']['columns'] ?? 'snake',
            relationCase: $config['case']['relations'] ?? 'snake',
            includeHidden: ! ($options['no-hidden'] ?? ! ($config['visibility']['include_hidden'] ?? true)),
            optionalNullables: $options['optional-nullables'] ?? $config['visibility']['optional_nullables'] ?? false,
            connection: $options['connection'] ?? $config['introspection']['connection'] ?? null,
            bailOnError: $options['strict'] ?? $config['pipeline']['bail_on_error'] ?? false,
            incremental: $options['incremental'] ?? $config['cache']['enabled'] ?? false,
            formatterEnabled: ! ($options['no-format'] ?? ! ($config['formatter']['enabled'] ?? false)),
            strict: $options['strict'] ?? false,
        );
    }
}
