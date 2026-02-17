<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

interface TypeMapperContract
{
    /**
     * Check if this mapper supports resolving the given type.
     */
    public function supports(string $type): bool;

    /**
     * Resolve the given type to a TypeScript type string.
     *
     * @param array<string, mixed> $parameters
     */
    public function resolve(string $type, array $parameters = []): string;
}
