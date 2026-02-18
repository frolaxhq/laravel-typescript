<?php

declare(strict_types=1);

namespace Frolax\Typescript\Discovery;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Frolax\Typescript\Contracts\ModelDiscoveryContract;
use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\ModelReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class ModelDiscovery implements ModelDiscoveryContract
{
    /**
     * Discover Eloquent models in configured paths.
     *
     * @return Collection<int, ModelReference>
     */
    public function discover(GenerationConfig $config): Collection
    {
        $paths = array_merge($config->paths, $config->additionalPaths);

        if ($config->autoDiscover) {
            $paths = array_unique(array_merge($paths, $this->discoverComposerPaths()));
        }

        if (empty($paths)) {
            $paths = [app_path('Models')];
        }

        return collect($paths)
            ->filter(fn (string $path) => is_dir($path))
            ->flatMap(fn (string $path) => $this->scanPath($path))
            ->unique(fn (ModelReference $ref) => $ref->className)
            ->when(
                ! empty($config->includedModels),
                fn (Collection $models) => $models->filter(
                    fn (ModelReference $ref) => in_array($ref->shortName, $config->includedModels)
                        || in_array($ref->className, $config->includedModels)
                )
            )
            ->when(
                ! empty($config->excludedModels),
                fn (Collection $models) => $models->reject(
                    fn (ModelReference $ref) => in_array($ref->shortName, $config->excludedModels)
                        || in_array($ref->className, $config->excludedModels)
                )
            )
            ->when(
                $config->specificModel !== null,
                fn (Collection $models) => $models->filter(
                    fn (ModelReference $ref) => $ref->shortName === $config->specificModel
                        || $ref->className === $config->specificModel
                )
            )
            ->sortBy(fn (ModelReference $ref) => $ref->shortName)
            ->values();
    }

    /**
     * Discover all PSR-4 paths defined in composer.json, excluding vendor.
     *
     * @return array<string>
     */
    private function discoverComposerPaths(): array
    {
        $autoloadPath = $this->findComposerAutoloadPath();

        if (! $autoloadPath) {
            return [];
        }

        $composer = require $autoloadPath;
        $prefixes = $composer->getPrefixesPsr4();
        $paths = [];

        foreach ($prefixes as $namespace => $prefixPaths) {
            foreach ($prefixPaths as $path) {
                $path = realpath($path) ?: $path;

                if (! Str::contains($path, '/vendor/') && ! Str::contains($path, '\\vendor\\')) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }

    private function findComposerAutoloadPath(): ?string
    {
        $potentialPaths = [
            base_path('vendor/autoload.php'),
            __DIR__.'/../../vendor/autoload.php',
            __DIR__.'/../../../vendor/autoload.php',
            getcwd().'/vendor/autoload.php',
        ];

        foreach ($potentialPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Scan a directory path for Eloquent model classes.
     *
     * @return Collection<int, ModelReference>
     */
    private function scanPath(string $path): Collection
    {
        try {
            $classMap = ClassMapGenerator::createMap($path);
        } catch (\Throwable) {
            return collect();
        }

        return collect($classMap)
            ->keys()
            ->filter(function (string $class) {
                try {
                    if (! class_exists($class)) {
                        return false;
                    }

                    $reflection = new ReflectionClass($class);

                    return $reflection->isSubclassOf(Model::class)
                        && ! $reflection->isAbstract();
                } catch (\Throwable) {
                    return false;
                }
            })
            ->map(function (string $class) use ($classMap) {
                $reflection = new ReflectionClass($class);

                /** @var Model $instance */
                $instance = $reflection->newInstance();

                return new ModelReference(
                    className: $class,
                    shortName: Str::afterLast($class, '\\'),
                    filePath: $classMap[$class],
                    connection: $instance->getConnectionName(),
                );
            })
            ->values();
    }
}
