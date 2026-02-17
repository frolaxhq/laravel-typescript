<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * A resolved relation ready for output writing.
 */
final readonly class ResolvedRelation
{
    public function __construct(
        /** Property name for the output */
        public string $name,
        /** Resolved TypeScript type string */
        public string $tsType,
        /** Whether this property is optional */
        public bool $optional = false,
        /** Whether a circular reference was detected */
        public bool $isCircular = false,
        /** Warning message if applicable */
        public ?string $warning = null,
    ) {}
}
