<?php

declare(strict_types=1);

namespace Frolax\Typescript\Support;

use Illuminate\Support\Str;

/**
 * Utility for converting between naming conventions.
 */
class CaseFormatter
{
    /**
     * Format a string to the given case convention.
     */
    public function format(string $value, string $case): string
    {
        return match ($case) {
            'snake' => Str::snake($value),
            'camel' => Str::camel($value),
            'pascal' => Str::studly($value),
            'kebab' => Str::kebab($value),
            default => $value,
        };
    }

    /**
     * Format a property name for TypeScript output.
     */
    public function formatProperty(string $name, string $case): string
    {
        return $this->format($name, $case);
    }

    /**
     * Convert an interface name (model name) to the appropriate format.
     */
    public function formatTypeName(string $name, bool $plural = false): string
    {
        return $plural ? Str::plural($name) : $name;
    }
}
