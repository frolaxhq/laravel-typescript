<?php

declare(strict_types=1);

namespace Frolax\Typescript\Resolvers;

use Frolax\Typescript\Data\GenerationConfig;
use ReflectionClass;

/**
 * Context passed to type resolvers with model-level information.
 */
final readonly class ResolverContext
{
    public function __construct(
        public GenerationConfig $config,
        public ReflectionClass $reflectionModel,
    ) {}
}
