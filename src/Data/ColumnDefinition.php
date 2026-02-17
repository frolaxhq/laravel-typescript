<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Definition of a model column for type resolution.
 */
final readonly class ColumnDefinition
{
    public function __construct(
        public string $name,
        /** Normalized DB type (e.g. "string", "integer", "uuid") */
        public string $dbType,
        /** Cast type if set (e.g. "datetime", "App\Enums\Status", "accessor") */
        public ?string $castType = null,
        public bool $nullable = false,
        public mixed $default = null,
        public bool $hidden = false,
        public bool $fillable = false,
        public bool $isAccessor = false,
        public bool $isPrimaryKey = false,
        public bool $isTimestamp = false,
        /** If this column has a forced type override via $interfaces */
        public ?string $forcedType = null,
    ) {}
}
