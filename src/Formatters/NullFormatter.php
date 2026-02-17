<?php

declare(strict_types=1);

namespace Frolax\Typescript\Formatters;

use Frolax\Typescript\Contracts\FormatterContract;

/**
 * No-op formatter — used when formatting is disabled.
 */
class NullFormatter implements FormatterContract
{
    public function name(): string
    {
        return 'none';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function format(string $content, string $filePath = 'stdin.ts'): string
    {
        return $content;
    }

    public function formatDirectory(string $directory): void
    {
        // No-op
    }
}
