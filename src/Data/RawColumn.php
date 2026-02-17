<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Raw column information from database schema introspection.
 */
final readonly class RawColumn
{
    public function __construct(
        public string $name,
        /** Normalized canonical type (e.g. "string", "integer", "uuid") */
        public string $type,
        /** Original DB-specific type string (e.g. "varchar(255)", "char(36)") */
        public string $rawType,
        public bool $nullable = false,
        public mixed $default = null,
        public bool $autoIncrement = false,
        public bool $unique = false,
    ) {}
}
