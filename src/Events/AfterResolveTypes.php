<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\ModelMetadata;
use Illuminate\Support\Collection;

final readonly class AfterResolveTypes
{
    public function __construct(
        public ModelMetadata $metadata,
        public Collection $resolvedProperties,
        public GenerationConfig $config,
    ) {}
}
