<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Writers\WriterOutput;

final readonly class AfterWrite
{
    public function __construct(
        public WriterOutput $output,
        public GenerationConfig $config,
    ) {}
}
