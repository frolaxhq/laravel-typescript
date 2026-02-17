<?php

declare(strict_types=1);

namespace Frolax\Typescript\Relations;

use Frolax\Typescript\Contracts\RelationResolverContract;
use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\RelationDefinition;
use Frolax\Typescript\Data\ResolvedRelation;
use Frolax\Typescript\Support\CaseFormatter;
use Illuminate\Support\Collection;

class RelationResolver implements RelationResolverContract
{
    /**
     * Relation types that return collections (arrays in TS).
     */
    private const COLLECTION_TYPES = [
        'BelongsToMany', 'HasMany', 'HasManyThrough',
        'MorphToMany', 'MorphMany', 'MorphedByMany',
    ];

    /** @var list<string> Track visited models for circular reference detection */
    private array $visitedStack = [];

    public function __construct(
        private readonly CaseFormatter $caseFormatter = new CaseFormatter,
    ) {}

    /**
     * @param  Collection<int, RelationDefinition>  $relations
     * @return Collection<int, ResolvedRelation>
     */
    public function resolveAll(
        Collection $relations,
        string $currentModel,
        GenerationConfig $config,
    ): Collection {
        $this->visitedStack = [$currentModel];

        return $relations->map(function (RelationDefinition $relation) use ($currentModel) {
            return $this->resolve($relation, $currentModel, 0);
        })->values();
    }

    public function resolve(
        RelationDefinition $relation,
        string $currentModel,
        int $depth,
    ): ResolvedRelation {
        $name = $this->caseFormatter->format($relation->name, 'snake'); // Will be re-formatted by writer
        $warning = null;

        // Check naming collision
        if (strtolower($relation->name) === strtolower($currentModel)) {
            $warning = "Relation '{$relation->name}' on model '{$currentModel}' has the same name as the model. This may cause confusion.";
        }

        // Check circular reference
        $isCircular = in_array($relation->relatedShortName, $this->visitedStack);

        // Determine TS type
        $relatedType = $relation->relatedShortName;

        // Handle MorphTo with union types
        if ($relation->type === 'MorphTo' && str_contains($relation->relatedModel, '|')) {
            $tsType = $relation->relatedModel; // Already formatted as UnionType
        } elseif (in_array($relation->type, self::COLLECTION_TYPES)) {
            $tsType = "{$relatedType}[]";
        } else {
            $tsType = $relatedType;
        }

        // Add nullability
        if ($relation->nullable) {
            $tsType .= ' | null';
        }

        return new ResolvedRelation(
            name: $name,
            tsType: $tsType,
            optional: false, // Optionality is applied by the writer based on config
            isCircular: $isCircular,
            warning: $warning,
        );
    }
}
