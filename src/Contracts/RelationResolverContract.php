<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\RelationDefinition;
use Frolax\Typescript\Data\ResolvedRelation;
use Illuminate\Support\Collection;

interface RelationResolverContract
{
    /**
     * Resolve all relations for a model into TypeScript type representations.
     *
     * @param  Collection<int, RelationDefinition>  $relations
     * @return Collection<int, ResolvedRelation>
     */
    public function resolveAll(
        Collection $relations,
        string $currentModel,
        GenerationConfig $config,
    ): Collection;

    /**
     * Resolve a single relation definition.
     */
    public function resolve(
        RelationDefinition $relation,
        string $currentModel,
        int $depth,
    ): ResolvedRelation;
}
