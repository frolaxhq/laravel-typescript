<?php

declare(strict_types=1);

namespace Frolax\Typescript\Metadata;

use Frolax\Typescript\Data\AccessorDefinition;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Resolves accessor/mutator definitions from a model.
 */
class AccessorResolver
{
    /**
     * Resolve all accessors from the model.
     *
     * @param  list<string>  $dbColumnNames
     * @param  array<string, string>  $overrides
     * @return Collection<int, AccessorDefinition>
     */
    public function resolve(ReflectionClass $reflection, Model $instance, array $dbColumnNames, array $overrides = []): Collection
    {
        $accessors = collect();

        // Find new-style Attribute accessors
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            if ($method->isStatic() || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (! $returnType instanceof ReflectionNamedType) {
                continue;
            }

            if ($returnType->getName() !== Attribute::class) {
                continue;
            }

            $name = Str::snake($method->getName());
            $forcedType = $overrides[$name] ?? null;

            // Skip if this is an actual DB column (it'll be handled as a column with accessor cast)
            if (in_array($name, $dbColumnNames)) {
                continue;
            }

            $accessorDef = $this->resolveAttributeAccessor($method, $name, $instance, $forcedType);
            if ($accessorDef) {
                $accessors->push($accessorDef);
            }
        }

        // Find traditional getXAttribute accessors
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            $methodName = $method->getName();

            if (! str_starts_with($methodName, 'get') || ! str_ends_with($methodName, 'Attribute')) {
                continue;
            }

            if ($methodName === 'getAttribute') {
                continue;
            }

            $name = Str::snake(
                Str::between($methodName, 'get', 'Attribute')
            );

            if (in_array($name, $dbColumnNames)) {
                continue;
            }

            if ($accessors->contains(fn (AccessorDefinition $a) => $a->name === $name)) {
                continue;
            }

            $forcedType = $overrides[$name] ?? null;
            $accessorDef = $this->resolveTraditionalAccessor($method, $name, $forcedType);
            if ($accessorDef) {
                $accessors->push($accessorDef);
            }
        }

        return $accessors;
    }

    /**
     * Resolve a new-style Attribute accessor.
     */
    private function resolveAttributeAccessor(
        ReflectionMethod $method,
        string $name,
        Model $instance,
        ?string $forcedType = null,
    ): ?AccessorDefinition {
        try {
            /** @var Attribute $attribute */
            $attribute = $method->invoke($instance);

            if (! $attribute instanceof Attribute || $attribute->get === null) {
                return new AccessorDefinition(
                    name: $name,
                    style: 'attribute',
                    forcedType: $forcedType ?? $this->extractTypeFromDocblock($method),
                );
            }

            $closureReflection = new ReflectionFunction($attribute->get);
            $forcedType = $forcedType ?? $this->extractTypeFromDocblock($method);
            $returnType = $closureReflection->getReturnType();

            if (! $returnType instanceof ReflectionNamedType) {
                return new AccessorDefinition(
                    name: $name,
                    style: 'attribute',
                    forcedType: $forcedType,
                );
            }

            $typeName = $returnType->getName();
            $isNullable = $returnType->allowsNull();

            // Check if it's an enum
            $isEnum = enum_exists($typeName);

            return new AccessorDefinition(
                name: $name,
                style: 'attribute',
                returnType: $typeName,
                isNullable: $isNullable,
                enumClass: $isEnum ? $typeName : null,
                forcedType: $forcedType,
            );
        } catch (\Throwable) {
            return new AccessorDefinition(
                name: $name,
                style: 'attribute',
                forcedType: $forcedType,
            );
        }
    }

    /**
     * Resolve a traditional getXAttribute accessor.
     */
    private function resolveTraditionalAccessor(
        ReflectionMethod $method,
        string $name,
        ?string $forcedType = null,
    ): ?AccessorDefinition {
        $returnType = $method->getReturnType();

        if (! $returnType instanceof ReflectionNamedType) {
            return new AccessorDefinition(
                name: $name,
                style: 'traditional',
                forcedType: $forcedType ?? $this->extractTypeFromDocblock($method),
            );
        }

        $typeName = $returnType->getName();
        $isNullable = $returnType->allowsNull();
        $isEnum = enum_exists($typeName);

        return new AccessorDefinition(
            name: $name,
            style: 'traditional',
            returnType: $typeName,
            isNullable: $isNullable,
            enumClass: $isEnum ? $typeName : null,
            forcedType: $forcedType ?? $this->extractTypeFromDocblock($method),
        );
    }

    /**
     * Extract a TypeScript type from a docblock @return tag.
     */
    private function extractTypeFromDocblock(ReflectionMethod $method): ?string
    {
        $docComment = $method->getDocComment();
        if (! $docComment) {
            return null;
        }

        // Match @return type
        if (preg_match('/@return\s+(.+)/', $docComment, $matches)) {
            $type = trim($matches[1]);

            // If it starts with a uppercase letter and looks like a TS type (not PSR-5 FQCN with backslash)
            // or if it's explicitly a TS type like {original: string}
            if (str_contains($type, '{') || str_contains($type, '|') || str_contains($type, '<')) {
                return $type;
            }

            // Simple class names or TS types
            if (! str_contains($type, '\\') && preg_match('/^[A-Z][A-Za-z0-9_]*$/', $type)) {
                return $type;
            }
        }

        return null;
    }
}
