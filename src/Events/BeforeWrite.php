<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\GenerationResult;

final readonly class BeforeWrite
{
    public function __construct(
        public GenerationResult $result,
        public GenerationConfig $config,
    ) {}
}
