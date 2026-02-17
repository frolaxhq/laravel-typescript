<?php

declare(strict_types=1);

namespace Frolax\Typescript\Contracts;

/**
 * Contract for output formatters (Prettier, Biome, etc.)
 */
interface FormatterContract
{
    /**
     * Get the formatter name.
     */
    public function name(): string;

    /**
     * Check if the formatter binary is available.
     */
    public function isAvailable(): bool;

    /**
     * Format file content.
     */
    public function format(string $content, string $filePath = 'stdin.ts'): string;

    /**
     * Format files in a directory.
     */
    public function formatDirectory(string $directory): void;
}
