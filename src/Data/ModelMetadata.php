<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

use Illuminate\Support\Collection;

/**
 * Full metadata extracted from a model, ready for type resolution.
 */
final readonly class ModelMetadata
{
    /**
     * @param  Collection<int, ColumnDefinition>  $columns
     * @param  Collection<int, AccessorDefinition>  $accessors
     * @param  Collection<int, RelationDefinition>  $relations
     * @param  array<string, string>  $casts
     * @param  list<string>  $hidden
     * @param  list<string>  $visible
     * @param  list<string>  $fillable
     * @param  list<string>  $guarded
     * @param  list<string>  $appends
     * @param  array<string, array{type?: string, nullable?: bool, import?: string}>|null  $interfaceOverrides
     * @param  array<string, string>|null  $sumDefinitions
     */
    public function __construct(
        public string $className,
        public string $shortName,
        public string $table,
        public string $connection,
        public string $primaryKey,
        public string $primaryKeyType,
        public bool $incrementing,
        public Collection $columns,
        public Collection $accessors,
        public Collection $relations,
        public array $casts = [],
        public array $hidden = [],
        public array $visible = [],
        public array $fillable = [],
        public array $guarded = ['*'],
        public array $appends = [],
        public bool $usesTimestamps = true,
        public ?array $interfaceOverrides = null,
        public ?array $sumDefinitions = null,
    ) {}
}
