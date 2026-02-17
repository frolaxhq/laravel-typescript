<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

use Illuminate\Support\Collection;

/**
 * Result of generating TypeScript for a single model.
 */
final readonly class ModelGenerationResult
{
    /**
     * @param  Collection<int, array{name: string, tsType: string, optional: bool, section: string}>  $properties
     * @param  Collection<int, ResolvedRelation>  $relations
     * @param  Collection<int, ResolvedRelation>  $counts
     * @param  Collection<int, ResolvedRelation>  $exists
     * @param  Collection<int, ResolvedRelation>  $sums
     * @param  Collection<int, EnumDefinition>  $enums
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $shortName,
        public string $className,
        public Collection $properties,
        public Collection $relations,
        public Collection $counts,
        public Collection $exists,
        public Collection $sums,
        public Collection $enums,
        public array $fillable = [],
        public array $warnings = [],
    ) {}
}
