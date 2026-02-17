<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Definition of a PHP backed enum for TypeScript output.
 */
final readonly class EnumDefinition
{
    /**
     * @param array<string, string|int> $cases Enum case name => backing value
     * @param array<string, string> $comments Enum case name => doc comment
     */
    public function __construct(
        /** FQCN of the enum class */
        public string $className,
        /** Short name of the enum */
        public string $shortName,
        /** Backing type: 'string' or 'int' */
        public string $backingType,
        /** Map of case name to backing value */
        public array $cases = [],
        /** Map of case name to doc comment */
        public array $comments = [],
    ) {}
}
