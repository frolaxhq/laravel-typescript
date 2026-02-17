<?php

declare(strict_types=1);

namespace Frolax\Typescript\Formatters;

use Frolax\Typescript\Contracts\FormatterContract;
use Illuminate\Support\Facades\Process;

/**
 * Prettier formatter integration.
 */
class PrettierFormatter implements FormatterContract
{
    public function __construct(
        private readonly string $binary = 'npx prettier',
        private readonly array $options = ['--parser' => 'typescript'],
    ) {}

    public function name(): string
    {
        return 'prettier';
    }

    public function isAvailable(): bool
    {
        $result = Process::run('npx prettier --version');

        return $result->successful();
    }

    public function format(string $content, string $filePath = 'stdin.ts'): string
    {
        $optionString = $this->buildOptions();
        $result = Process::input($content)->run(
            "{$this->binary} --stdin-filepath {$filePath} {$optionString}"
        );

        return $result->successful() ? $result->output() : $content;
    }

    public function formatDirectory(string $directory): void
    {
        $optionString = $this->buildOptions();
        Process::run("{$this->binary} --write \"{$directory}/**/*.ts\" {$optionString}");
    }

    private function buildOptions(): string
    {
        return collect($this->options)
            ->map(fn ($value, $key) => "{$key} {$value}")
            ->implode(' ');
    }
}
