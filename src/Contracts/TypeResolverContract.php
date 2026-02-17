<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\AccessorDefinition;
use Frolax\Typescript\Data\ColumnDefinition;
use Frolax\Typescript\Data\TypeResult;
use Frolax\Typescript\Resolvers\ResolverContext;

interface TypeResolverContract
{
    /**
     * Resolve a column definition to its TypeScript type.
     */
    public function resolve(ColumnDefinition $column, ResolverContext $context): TypeResult;

    /**
     * Resolve an accessor definition to its TypeScript type.
     */
    public function resolveAccessor(AccessorDefinition $accessor, ResolverContext $context): TypeResult;
}
