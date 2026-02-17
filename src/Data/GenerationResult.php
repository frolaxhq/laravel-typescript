<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

use Frolax\Typescript\Writers\WriterOutput;
use Illuminate\Support\Collection;

/**
 * Aggregate result of the full generation pipeline.
 */
final readonly class GenerationResult
{
    /**
     * @param  Collection<int, ModelGenerationResult>  $models
     * @param  Collection<int, EnumDefinition>  $enums  All unique enums across models
     * @param  list<string>  $warnings
     * @param  list<array{import: string, type: string}>  $imports
     */
    public function __construct(
        public Collection $models,
        public Collection $enums,
        public array $warnings = [],
        public array $imports = [],
        public ?WriterOutput $output = null,
    ) {}
}
