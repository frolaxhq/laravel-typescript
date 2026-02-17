<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Configuration for a specific writer.
 */
final readonly class WriterConfig
{
    public function __construct(
        public string $writer = 'interface',
        public string $enumStyle = 'const_object',
        public ?string $globalNamespace = null,
        public bool $plurals = false,
        public bool $apiResources = false,
        public bool $fillableTypes = false,
        public string $fillableSuffix = 'Fillable',
        public string $columnCase = 'snake',
        public string $relationCase = 'snake',
        public string $indent = '  ',
        public bool $perModelFiles = false,
        public bool $barrelExport = true,
        public string $enumDirectory = 'enums',
        public string $singleFileName = 'models.d.ts',
    ) {}

    /**
     * Create from a GenerationConfig.
     */
    public static function fromGenerationConfig(GenerationConfig $config): self
    {
        return new self(
            writer: $config->writer,
            enumStyle: $config->enumStyle,
            globalNamespace: $config->globalNamespace,
            plurals: $config->plurals,
            apiResources: $config->apiResources,
            fillableTypes: $config->fillableTypes,
            fillableSuffix: $config->fillableSuffix,
            columnCase: $config->columnCase,
            relationCase: $config->relationCase,
            perModelFiles: $config->perModelFiles ?? false,
        );
    }
}
