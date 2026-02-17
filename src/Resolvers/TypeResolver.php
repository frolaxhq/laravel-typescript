<?php

declare(strict_types=1);

namespace Frolax\Typescript\Resolvers;

use Frolax\Typescript\Contracts\TypeResolverContract;
use Frolax\Typescript\Data\AccessorDefinition;
use Frolax\Typescript\Data\ColumnDefinition;
use Frolax\Typescript\Data\EnumDefinition;
use Frolax\Typescript\Data\TypeResult;
use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Illuminate\Support\Str;

/**
 * Default type resolver implementing the 8-level precedence chain:
 *
 * Priority 1: $interfaces forced override
 * Priority 2: Explicit API resource type (if api_resources mode)
 * Priority 3: Enum cast (AsEnumCollection, AsEnumArrayObject)
 * Priority 4: Accessor return type
 * Priority 5: Cast type
 * Priority 6: DB column type
 * Priority 7: Custom user mapping
 * Priority 8: Default ('unknown')
 */
class TypeResolver implements TypeResolverContract
{
    public function __construct(
        private readonly TypeMapperRegistry $mapperRegistry,
    ) {}

    public function resolve(ColumnDefinition $column, ResolverContext $context): TypeResult
    {
        // Priority 1: Forced type override from $interfaces
        if ($column->forcedType !== null) {
            return new TypeResult(
                tsType: $column->forcedType,
                nullable: $column->nullable,
                source: 'override',
            );
        }

        // Priority 3: Enum cast
        if ($column->castType !== null) {
            $enumResult = $this->resolveEnumCast($column->castType, $column->nullable);
            if ($enumResult !== null) {
                return $enumResult;
            }
        }

        // Priority 5: Cast type (non-enum)
        if ($column->castType !== null) {
            $castResult = $this->resolveCastType($column->castType, $column->nullable);
            if ($castResult !== null) {
                return $castResult;
            }
        }

        // Priority 6: DB column type via mapper
        $dbResult = $this->resolveDbType($column->dbType, $column->nullable, $column->isTimestamp, $context);
        if ($dbResult !== null) {
            return $dbResult;
        }

        // Priority 8: Fallback
        return new TypeResult(
            tsType: 'unknown',
            nullable: $column->nullable,
            source: 'unknown',
        );
    }

    public function resolveAccessor(AccessorDefinition $accessor, ResolverContext $context): TypeResult
    {
        // Priority 1: Forced type override from $interfaces
        if ($accessor->forcedType !== null) {
            return new TypeResult(
                tsType: $accessor->forcedType,
                nullable: $accessor->isNullable,
                source: 'override',
            );
        }

        // If the accessor has an enum class, resolve as enum
        if ($accessor->enumClass !== null && enum_exists($accessor->enumClass)) {
            $enumDef = $this->buildEnumDefinition($accessor->enumClass);

            return new TypeResult(
                tsType: $enumDef->shortName,
                nullable: $accessor->isNullable,
                enum: $enumDef,
                source: 'accessor',
            );
        }

        // If the accessor has a return type, resolve it
        if ($accessor->returnType !== null) {
            return $this->resolvePhpType($accessor->returnType, $accessor->isNullable);
        }

        // No type information available
        return new TypeResult(
            tsType: 'unknown',
            nullable: $accessor->isNullable,
            source: 'accessor',
        );
    }

    /**
     * Resolve enum cast types like 'App\Enums\Status' or parameterized casts
     * like 'Illuminate\Database\Eloquent\Casts\AsEnumCollection:App\Enums\Status'.
     */
    private function resolveEnumCast(string $castType, bool $nullable): ?TypeResult
    {
        // Handle AsEnumCollection::of(Status::class) or key:ClassName format
        $enumClass = null;
        $isArray = false;

        // Check for parameterized enum casts
        $parameterizedCasts = [
            'Illuminate\Database\Eloquent\Casts\AsEnumCollection',
            'Illuminate\Database\Eloquent\Casts\AsEnumArrayObject',
        ];

        foreach ($parameterizedCasts as $parameterizedCast) {
            if (str_starts_with($castType, $parameterizedCast)) {
                $parts = explode(':', $castType, 2);
                $enumClass = $parts[1] ?? null;
                $isArray = true;
                break;
            }
        }

        // Direct enum class reference
        if ($enumClass === null && class_exists($castType) && enum_exists($castType)) {
            $enumClass = $castType;
        }

        if ($enumClass === null || ! enum_exists($enumClass)) {
            return null;
        }

        $enumDef = $this->buildEnumDefinition($enumClass);
        $tsType = $isArray ? "{$enumDef->shortName}[]" : $enumDef->shortName;

        return new TypeResult(
            tsType: $tsType,
            nullable: $nullable,
            enum: $enumDef,
            source: 'enum_cast',
        );
    }

    /**
     * Resolve non-enum cast types.
     */
    private function resolveCastType(string $castType, bool $nullable): ?TypeResult
    {
        // Strip parameters: 'decimal:2' → 'decimal'
        $baseCast = Str::before($castType, ':');
        $baseCastLower = strtolower($baseCast);

        // Special cast types
        $castMap = [
            'int' => 'number',
            'integer' => 'number',
            'real' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'string' => 'string',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'array' => 'Record<string, unknown>',
            'json' => 'Record<string, unknown>',
            'object' => 'Record<string, unknown>',
            'collection' => 'Record<string, unknown>',
            'date' => 'string',
            'datetime' => 'string',
            'immutable_date' => 'string',
            'immutable_datetime' => 'string',
            'timestamp' => 'string',
            'encrypted' => 'string',
            'encrypted:array' => 'Record<string, unknown>',
            'encrypted:collection' => 'Record<string, unknown>',
            'encrypted:object' => 'Record<string, unknown>',
            'hashed' => 'string',
        ];

        if (isset($castMap[$baseCastLower])) {
            return new TypeResult(
                tsType: $castMap[$baseCastLower],
                nullable: $nullable,
                source: 'cast',
            );
        }

        // Not a recognized cast, let it fall through to DB type
        return null;
    }

    /**
     * Resolve a raw DB type via the mapper registry.
     */
    private function resolveDbType(
        string $dbType,
        bool $nullable,
        bool $isTimestamp,
        ResolverContext $context,
    ): ?TypeResult {
        // Check for timestamp → Date config
        if ($isTimestamp && $context->config->timestampsAsDate) {
            return new TypeResult(
                tsType: 'Date',
                nullable: $nullable,
                source: 'db_type',
            );
        }

        // Try custom mappings first
        if (isset($context->config->customMappings[$dbType])) {
            return new TypeResult(
                tsType: $context->config->customMappings[$dbType],
                nullable: $nullable,
                source: 'custom_mapping',
            );
        }

        // Try mapper registry
        if ($this->mapperRegistry->supports($dbType)) {
            $tsType = $this->mapperRegistry->resolve($dbType);

            return new TypeResult(
                tsType: $tsType,
                nullable: $nullable,
                source: 'db_type',
            );
        }

        return null;
    }

    /**
     * Resolve a PHP type name to a TypeScript type.
     */
    private function resolvePhpType(string $phpType, bool $nullable): TypeResult
    {
        $phpMap = [
            'string' => 'string',
            'int' => 'number',
            'float' => 'number',
            'bool' => 'boolean',
            'array' => 'Record<string, unknown>',
            'object' => 'Record<string, unknown>',
            'null' => 'null',
            'true' => 'true',
            'false' => 'false',
            'mixed' => 'unknown',
            'void' => 'void',
            'Carbon\Carbon' => 'string',
            'Illuminate\Support\Carbon' => 'string',
            'Illuminate\Support\Collection' => 'unknown[]',
            'DateTime' => 'string',
            'DateTimeInterface' => 'string',
            'DateTimeImmutable' => 'string',
        ];

        if (isset($phpMap[$phpType])) {
            return new TypeResult(
                tsType: $phpMap[$phpType],
                nullable: $nullable,
                source: 'accessor',
            );
        }

        // If it's an enum, resolve it
        if (enum_exists($phpType)) {
            $enumDef = $this->buildEnumDefinition($phpType);

            return new TypeResult(
                tsType: $enumDef->shortName,
                nullable: $nullable,
                enum: $enumDef,
                source: 'accessor',
            );
        }

        // Unknown PHP type → unknown
        return new TypeResult(
            tsType: 'unknown',
            nullable: $nullable,
            source: 'accessor',
        );
    }

    /**
     * Build an EnumDefinition from a PHP enum class.
     */
    private function buildEnumDefinition(string $enumClass): EnumDefinition
    {
        $reflection = new \ReflectionEnum($enumClass);
        $shortName = Str::afterLast($enumClass, '\\');

        $backingType = 'string';
        if ($reflection->isBacked()) {
            $backingReflection = $reflection->getBackingType();
            $backingType = $backingReflection?->getName() === 'int' ? 'int' : 'string';
        }

        $cases = [];
        $comments = [];
        foreach ($reflection->getCases() as $case) {
            $value = $reflection->isBacked() ? $case->getBackingValue() : $case->getName();
            $cases[$case->getName()] = $value;

            $docComment = $case->getDocComment();
            if ($docComment) {
                $comments[$case->getName()] = trim(
                    preg_replace('/^\s*\/?(\*)+\s?/m', '', $docComment) ?? ''
                );
            }
        }

        return new EnumDefinition(
            className: $enumClass,
            shortName: $shortName,
            backingType: $backingType,
            cases: $cases,
            comments: $comments,
        );
    }
}
