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
     * @param list<string> $dbColumnNames
     * @return Collection<int, AccessorDefinition>
     */
    public function resolve(ReflectionClass $reflection, Model $instance, array $dbColumnNames): Collection
    {
        $accessors = collect();

        // Find new-style Attribute accessors
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
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

            // Skip if this is an actual DB column (it'll be handled as a column with accessor cast)
            if (in_array($name, $dbColumnNames)) {
                continue;
            }

            $accessorDef = $this->resolveAttributeAccessor($method, $name, $instance);
            if ($accessorDef) {
                $accessors->push($accessorDef);
            }
        }

        // Find traditional getXAttribute accessors
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
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

            // Skip if already found as new-style
            if ($accessors->contains(fn (AccessorDefinition $a) => $a->name === $name)) {
                continue;
            }

            $accessorDef = $this->resolveTraditionalAccessor($method, $name);
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
    ): ?AccessorDefinition {
        try {
            /** @var Attribute $attribute */
            $attribute = $method->invoke($instance);

            if (! $attribute instanceof Attribute || $attribute->get === null) {
                return new AccessorDefinition(
                    name: $name,
                    style: 'attribute',
                );
            }

            $closureReflection = new ReflectionFunction($attribute->get);
            $returnType = $closureReflection->getReturnType();

            if (! $returnType instanceof ReflectionNamedType) {
                return new AccessorDefinition(
                    name: $name,
                    style: 'attribute',
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
            );
        } catch (\Throwable) {
            return new AccessorDefinition(
                name: $name,
                style: 'attribute',
            );
        }
    }

    /**
     * Resolve a traditional getXAttribute accessor.
     */
    private function resolveTraditionalAccessor(
        ReflectionMethod $method,
        string $name,
    ): ?AccessorDefinition {
        $returnType = $method->getReturnType();

        if (! $returnType instanceof ReflectionNamedType) {
            return new AccessorDefinition(
                name: $name,
                style: 'traditional',
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
        );
    }
}
