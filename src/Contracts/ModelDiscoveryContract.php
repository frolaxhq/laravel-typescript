<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\ModelReference;
use Illuminate\Support\Collection;

interface ModelDiscoveryContract
{
    /**
     * Discover Eloquent models in configured paths.
     *
     * @return Collection<int, ModelReference>
     */
    public function discover(GenerationConfig $config): Collection;
}
