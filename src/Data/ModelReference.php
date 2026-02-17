<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Reference to a discovered Eloquent model.
 */
final readonly class ModelReference
{
    public function __construct(
        /** Fully qualified class name */
        public string $className,
        /** Short class name (e.g. "User") */
        public string $shortName,
        /** Absolute file path */
        public string $filePath,
        /** Model's database connection name (null = default) */
        public ?string $connection = null,
    ) {}
}
