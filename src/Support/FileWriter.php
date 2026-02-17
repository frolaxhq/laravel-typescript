<?php

declare(strict_types=1);

namespace Frolax\Typescript\Support;

use Illuminate\Support\Facades\File;

/**
 * Utility to write generated content to the file system.
 */
class FileWriter
{
    /**
     * Write files from a file map to disk.
     *
     * @param array<string, string> $fileMap  Path => content mapping
     * @param string $basePath  Base output directory
     */
    public function writeFiles(array $fileMap, string $basePath): void
    {
        File::ensureDirectoryExists($basePath);

        foreach ($fileMap as $relativePath => $content) {
            $fullPath = rtrim($basePath, '/') . '/' . ltrim($relativePath, '/');
            $directory = dirname($fullPath);

            File::ensureDirectoryExists($directory);
            File::put($fullPath, $content);
        }
    }

    /**
     * Write a single bundled file.
     */
    public function writeSingleFile(string $path, string $content): void
    {
        $directory = dirname($path);
        File::ensureDirectoryExists($directory);
        File::put($path, $content);
    }

    /**
     * Clean a directory of previously generated files.
     */
    public function cleanDirectory(string $path, string $extension = '.ts'): void
    {
        if (! File::isDirectory($path)) {
            return;
        }

        foreach (File::glob("{$path}/*{$extension}") as $file) {
            // Only delete auto-generated files (check header comment)
            $firstLine = fgets(fopen($file, 'r'));
            if ($firstLine !== false && str_contains($firstLine, 'auto-generated')) {
                File::delete($file);
            }
        }
    }
}
