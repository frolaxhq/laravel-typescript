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

        if (empty($paths)) {
            $paths = [app_path('Models')];
        }

        return collect($paths)
            ->filter(fn (string $path) => is_dir($path))
            ->flatMap(fn (string $path) => $this->scanPath($path))
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
                if (! class_exists($class)) {
                    return false;
                }

                $reflection = new ReflectionClass($class);

                return $reflection->isSubclassOf(Model::class)
                    && ! $reflection->isAbstract();
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
