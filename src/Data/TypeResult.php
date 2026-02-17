<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Result of resolving a PHP type to a TypeScript type.
 */
final readonly class TypeResult
{
    public function __construct(
        /** The resolved TypeScript type string (e.g. "string", "number", "Status") */
        public string $tsType,
        /** Whether the type is nullable (adds " | null") */
        public bool $nullable = false,
        /** Whether the property should be optional (adds "?") */
        public bool $optional = false,
        /** Associated enum definition, if the type is an enum */
        public ?EnumDefinition $enum = null,
        /** Source of the resolution ("override" | "accessor" | "enum_cast" | "cast" | "db_type" | "unknown") */
        public string $source = 'unknown',
    ) {}

    /**
     * Create a result for an unknown/unmapped type.
     */
    public static function unknown(): self
    {
        return new self(tsType: 'unknown', source: 'unknown');
    }

    /**
     * Create a result with a simple type string.
     */
    public static function simple(string $type, string $source = 'db_type'): self
    {
        return new self(tsType: $type, source: $source);
    }

    /**
     * Create a result for an array type.
     */
    public static function array(string $itemType, string $source = 'cast'): self
    {
        return new self(tsType: "{$itemType}[]", source: $source);
    }

    /**
     * Create a result for a circular reference.
     */
    public static function circularReference(string $modelName): self
    {
        return new self(tsType: $modelName, source: 'circular_ref');
    }

    /**
     * Create a result for a shallow reference (max depth reached).
     */
    public static function shallowReference(string $modelName): self
    {
        return new self(tsType: $modelName, source: 'shallow_ref');
    }

    /**
     * Get the full TS type string including nullability.
     */
    public function toTypeString(): string
    {
        return $this->nullable ? "{$this->tsType} | null" : $this->tsType;
    }
}
