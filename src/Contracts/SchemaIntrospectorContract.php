<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

use Frolax\Typescript\Data\RawColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface SchemaIntrospectorContract
{
    /**
     * Check if this introspector supports the given database driver.
     */
    public function supports(string $driver): bool;

    /**
     * Get all columns for the given model's table.
     *
     * @return Collection<int, RawColumn>
     */
    public function getColumns(Model $model): Collection;

    /**
     * Get the normalized type for a specific column.
     */
    public function getColumnType(Model $model, string $column): string;
}
