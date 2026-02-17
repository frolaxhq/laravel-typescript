<?php

declare(strict_types=1);

namespace Frolax\Typescript;

use Frolax\Typescript\Contracts\TypeMapperContract;
use Frolax\Typescript\Contracts\WriterContract;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Mappers\TypeMapperRegistry;

/**
 * Extension API for the laravel-typescript package.
 *
 * Usage:
 *   Typescript::extend(function (Typescript $ts) {
 *       $ts->registerMapper(new CustomMapper());
 *       $ts->registerWriter(new ZodWriter());
 *   });
 */
class Typescript
{
    /** @var list<callable> */
    private static array $extensions = [];

    /** @var list<TypeMapperContract> */
    private array $pendingMappers = [];

    /** @var list<WriterContract> */
    private array $pendingWriters = [];

    /**
     * Register an extension callback.
     */
    public static function extend(callable $callback): void
    {
        self::$extensions[] = $callback;
    }

    /**
     * Get all registered extensions.
     *
     * @return list<callable>
     */
    public static function getExtensions(): array
    {
        return self::$extensions;
    }

    /**
     * Clear all registered extensions (for testing).
     */
    public static function clearExtensions(): void
    {
        self::$extensions = [];
    }

    /**
     * Register a custom type mapper.
     */
    public function registerMapper(TypeMapperContract $mapper): self
    {
        $this->pendingMappers[] = $mapper;

        return $this;
    }

    /**
     * Register a custom writer.
     */
    public function registerWriter(WriterContract $writer): self
    {
        $this->pendingWriters[] = $writer;

        return $this;
    }

    /**
     * Apply all pending extensions to the registries.
     */
    public function applyExtensions(
        TypeMapperRegistry $mapperRegistry,
        SchemaIntrospectorRegistry $introspectorRegistry,
    ): void {
        // Run all extension callbacks
        foreach (self::$extensions as $callback) {
            $callback($this);
        }

        // Apply pending mappers
        foreach ($this->pendingMappers as $mapper) {
            $mapperRegistry->register($mapper);
        }
    }

    /**
     * Get all pending writers.
     *
     * @return list<WriterContract>
     */
    public function getPendingWriters(): array
    {
        return $this->pendingWriters;
    }
}
