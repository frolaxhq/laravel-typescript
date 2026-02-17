<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Definition of an Eloquent relationship.
 */
final readonly class RelationDefinition
{
    public function __construct(
        /** Relation method name */
        public string $name,
        /** Relation type (e.g. "BelongsTo", "HasMany", "MorphToMany") */
        public string $type,
        /** Fully qualified related model class name */
        public string $relatedModel,
        /** Short name of related model */
        public string $relatedShortName,
        /** Whether the relation return type is nullable */
        public bool $nullable = false,
        /** Whether this is a "many" relation (HasMany, BelongsToMany, etc.) */
        public bool $isCollection = false,
    ) {}
}
