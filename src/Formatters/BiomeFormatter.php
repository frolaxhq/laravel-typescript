<?php

declare(strict_types=1);

namespace Frolax\Typescript\Formatters;

use Frolax\Typescript\Contracts\FormatterContract;
use Illuminate\Support\Facades\Process;

/**
 * Biome formatter integration.
 */
class BiomeFormatter implements FormatterContract
{
    public function __construct(
        private readonly string $binary = 'npx @biomejs/biome',
    ) {}

    public function name(): string
    {
        return 'biome';
    }

    public function isAvailable(): bool
    {
        $result = Process::run('npx @biomejs/biome --version');

        return $result->successful();
    }

    public function format(string $content, string $filePath = 'stdin.ts'): string
    {
        $result = Process::input($content)->run(
            "{$this->binary} format --stdin-file-path {$filePath}"
        );

        return $result->successful() ? $result->output() : $content;
    }

    public function formatDirectory(string $directory): void
    {
        Process::run("{$this->binary} format --write \"{$directory}\"");
    }
}
