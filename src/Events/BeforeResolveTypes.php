<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\ModelMetadata;

final readonly class BeforeResolveTypes
{
    public function __construct(
        public ModelMetadata $metadata,
        public GenerationConfig $config,
    ) {}
}
