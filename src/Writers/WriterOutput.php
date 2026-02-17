<?php

declare(strict_types=1);

namespace Frolax\Typescript\Writers;

/**
 * Output from a writer.
 */
final readonly class WriterOutput
{
    /**
     * @param  array<string, string>  $files  Map of file path → content
     * @param  string|null  $stdout  Content for stdout (CLI output mode)
     */
    public function __construct(
        public array $files = [],
        public ?string $stdout = null,
    ) {}
}
