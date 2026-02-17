<?php

declare(strict_types=1);

namespace Frolax\Typescript\Metadata;

use Frolax\Typescript\Contracts\ModelMetadataExtractorContract;
use Frolax\Typescript\Data\ColumnDefinition;
use Frolax\Typescript\Data\ModelMetadata;
use Frolax\Typescript\Data\ModelReference;
use Frolax\Typescript\Data\RawColumn;
use Frolax\Typescript\Data\RelationDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class ModelMetadataExtractor implements ModelMetadataExtractorContract
{
    private readonly AccessorResolver $accessorResolver;

    public function __construct(?AccessorResolver $accessorResolver = null)
    {
        $this->accessorResolver = $accessorResolver ?? new AccessorResolver;
    }

    /**
     * @param  Collection<int, RawColumn>  $columns
     */
    public function extract(ModelReference $model, Collection $columns): ModelMetadata
    {
        $reflection = new ReflectionClass($model->className);
        /** @var Model $instance */
        $instance = $reflection->newInstance();

        $casts = $instance->getCasts();
        $hidden = $instance->getHidden();
        $visible = $instance->getVisible();
        $fillable = $instance->getFillable();
        $guarded = $instance->getGuarded();
        $appends = $instance->getAppends() ?? [];

        // Read interface overrides ($interfaces property)
        $interfaceOverrides = property_exists($instance, 'interfaces') ? $instance->interfaces : null;
        $sumDefinitions = property_exists($instance, 'sums') ? $instance->sums : null;

        // Build column definitions
        $columnDefs = $columns->map(function (RawColumn $rawCol) use ($casts, $hidden, $fillable, $instance, $appends) {
            return new ColumnDefinition(
                name: $rawCol->name,
                dbType: $rawCol->type,
                castType: $casts[$rawCol->name] ?? null,
                nullable: $rawCol->nullable,
                default: $rawCol->default,
                hidden: in_array($rawCol->name, $hidden),
                fillable: in_array($rawCol->name, $fillable),
                isAccessor: in_array($rawCol->name, $appends),
                isPrimaryKey: $rawCol->name === $instance->getKeyName(),
                isTimestamp: in_array($rawCol->name, [
                    $instance->getCreatedAtColumn(),
                    $instance->getUpdatedAtColumn(),
                ]),
            );
        });

        // Resolve accessors (non-column appended attributes)
        $dbColumnNames = $columns->pluck('name')->toArray();
        $accessors = $this->accessorResolver->resolve($reflection, $instance, $dbColumnNames, $interfaceOverrides ?? []);

        // Resolve relations
        $relations = $this->resolveRelations($reflection, $instance);

        return new ModelMetadata(
            className: $model->className,
            shortName: $model->shortName,
            table: $instance->getTable(),
            connection: $instance->getConnectionName() ?? config('database.default'),
            primaryKey: $instance->getKeyName(),
            primaryKeyType: $instance->getKeyType(),
            incrementing: $instance->getIncrementing(),
            columns: $columnDefs,
            accessors: $accessors,
            relations: $relations,
            casts: $casts,
            hidden: $hidden,
            visible: $visible,
            fillable: $fillable,
            guarded: $guarded,
            appends: $appends,
            usesTimestamps: $instance->usesTimestamps(),
            interfaceOverrides: $interfaceOverrides,
            sumDefinitions: $sumDefinitions,
        );
    }

    /**
     * Resolve all relations from the model using reflection.
     *
     * @return Collection<int, RelationDefinition>
     */
    private function resolveRelations(ReflectionClass $reflection, Model $instance): Collection
    {
        return collect(get_class_methods($instance))
            ->map(fn (string $method) => new ReflectionMethod($instance, $method))
            ->reject(fn (ReflectionMethod $m) => $m->isStatic()
                || $m->isAbstract()
                || $m->getDeclaringClass()->getName() === Model::class
                || $m->getNumberOfParameters() > 0
            )
            ->filter(function (ReflectionMethod $method) {
                $returnType = $method->getReturnType();
                if (! $returnType instanceof ReflectionNamedType) {
                    return false;
                }

                return is_subclass_of($returnType->getName(), Relation::class);
            })
            ->map(function (ReflectionMethod $method) use ($instance) {
                try {
                    /** @var Relation $relation */
                    $relation = $method->invoke($instance);

                    if (! $relation instanceof Relation) {
                        return null;
                    }

                    $relationType = Str::afterLast(get_class($relation), '\\');
                    $related = get_class($relation->getRelated());
                    $relatedShort = Str::afterLast($related, '\\');

                    $isCollection = in_array($relationType, [
                        'BelongsToMany', 'HasMany', 'HasManyThrough',
                        'MorphToMany', 'MorphMany', 'MorphedByMany',
                    ]);

                    // Check nullable from return type
                    $nullable = $this->isReturnTypeNullable($method);

                    return new RelationDefinition(
                        name: $method->getName(),
                        type: $relationType,
                        relatedModel: $related,
                        relatedShortName: $relatedShort,
                        nullable: $nullable,
                        isCollection: $isCollection,
                    );
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->values();
    }

    /**
     * Check if a method's return type allows null.
     */
    private function isReturnTypeNullable(ReflectionMethod $method): bool
    {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return false;
        }

        if ($returnType instanceof ReflectionUnionType) {
            foreach ($returnType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType && $type->getName() === 'null') {
                    return true;
                }
            }
        }

        if ($returnType instanceof ReflectionNamedType) {
            return $returnType->allowsNull();
        }

        return false;
    }
}
