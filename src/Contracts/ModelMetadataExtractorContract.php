<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\ModelMetadata;
use Frolax\Typescript\Data\ModelReference;
use Frolax\Typescript\Data\RawColumn;
use Illuminate\Support\Collection;

interface ModelMetadataExtractorContract
{
    /**
     * Extract full metadata from a model, including casts, accessors, relations, etc.
     *
     * @param Collection<int, RawColumn> $columns
     */
    public function extract(ModelReference $model, Collection $columns): ModelMetadata;
}
