<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;

final readonly class BeforeDiscover
{
    public function __construct(
        public GenerationConfig $config,
    ) {}
}
