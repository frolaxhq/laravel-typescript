<?php

declare(strict_types=1);

namespace Frolax\Typescript\Introspection;

use Frolax\Typescript\Contracts\SchemaIntrospectorContract;
use Frolax\Typescript\Data\RawColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Fallback introspector for unsupported database drivers.
 * Uses model metadata (casts, fillable) to infer column types.
 */
class FallbackSchemaIntrospector implements SchemaIntrospectorContract
{
    public function supports(string $driver): bool
    {
        return true; // Accepts any driver as last resort
    }

    /**
     * @return Collection<int, RawColumn>
     */
    public function getColumns(Model $model): Collection
    {
        // Use fillable + guarded as a hint for column names
        $fillable = $model->getFillable();
        $casts = $model->getCasts();

        // Combine fillable columns with cast keys
        $columnNames = array_unique(array_merge($fillable, array_keys($casts)));

        // Always include primary key
        if (! in_array($model->getKeyName(), $columnNames)) {
            array_unshift($columnNames, $model->getKeyName());
        }

        // Always include timestamps if applicable
        if ($model->usesTimestamps()) {
            $columnNames[] = $model->getCreatedAtColumn();
            $columnNames[] = $model->getUpdatedAtColumn();
        }

        $columnNames = array_unique($columnNames);

        return collect($columnNames)->map(function (string $name) use ($casts, $model) {
            $castType = $casts[$name] ?? null;
            $type = $this->inferTypeFromCast($castType, $name, $model);

            return new RawColumn(
                name: $name,
                type: $type,
                rawType: 'unknown',
                nullable: false, // Cannot determine from metadata alone
                default: null,
                autoIncrement: $name === $model->getKeyName() && $model->getIncrementing(),
            );
        });
    }

    public function getColumnType(Model $model, string $column): string
    {
        $casts = $model->getCasts();

        return $this->inferTypeFromCast($casts[$column] ?? null, $column, $model);
    }

    private function inferTypeFromCast(?string $cast, string $name, Model $model): string
    {
        if ($cast === null) {
            // Guess from name conventions
            if ($name === $model->getKeyName()) {
                return $model->getKeyType();
            }

            return 'string';
        }

        return match (strtolower(explode(':', $cast)[0])) {
            'int', 'integer' => 'integer',
            'real', 'float', 'double' => 'float',
            'decimal' => 'decimal',
            'string' => 'string',
            'bool', 'boolean' => 'boolean',
            'date', 'datetime', 'immutable_date', 'immutable_datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'array', 'json', 'object', 'collection' => 'json',
            default => 'string',
        };
    }
}
