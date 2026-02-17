<?php

declare(strict_types=1);

namespace Frolax\Typescript\Events;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\ModelReference;
use Illuminate\Support\Collection;

final readonly class AfterDiscover
{
    /**
     * @param  Collection<int, ModelReference>  $models
     */
    public function __construct(
        public Collection $models,
        public GenerationConfig $config,
    ) {}
}
