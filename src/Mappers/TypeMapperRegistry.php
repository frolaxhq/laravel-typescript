<?php

declare(strict_types=1);

namespace Frolax\Typescript\Mappers;

use Frolax\Typescript\Contracts\TypeMapperContract;

/**
 * Registry of type mappers checked in priority order.
 */
class TypeMapperRegistry
{
    /** @var list<TypeMapperContract> */
    private array $mappers = [];

    public function __construct()
    {
        $this->register(new DefaultTypeMapper);
    }

    /**
     * Register a type mapper (higher priority, checked first).
     */
    public function register(TypeMapperContract $mapper): void
    {
        array_unshift($this->mappers, $mapper);
    }

    /**
     * Check if any registered mapper supports the given type.
     */
    public function supports(string $type): bool
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the given type using the first supporting mapper.
     */
    public function resolve(string $type, array $parameters = []): string
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($type)) {
                return $mapper->resolve($type, $parameters);
            }
        }

        return 'unknown';
    }
}
