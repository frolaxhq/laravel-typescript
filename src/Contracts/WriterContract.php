<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\GenerationResult;
use Frolax\Typescript\Data\WriterConfig;
use Frolax\Typescript\Writers\WriterOutput;

interface WriterContract
{
    /**
     * Get the writer's unique name identifier.
     */
    public function name(): string;

    /**
     * Write the generation result to output format.
     */
    public function write(GenerationResult $result, WriterConfig $config): WriterOutput;
}
