<?php

declare(strict_types=1);

namespace Frolax\Typescript;

use Frolax\Typescript\Commands\GenerateTypescriptCommand;
use Frolax\Typescript\Commands\InspectModelCommand;
use Frolax\Typescript\Commands\ShowMappingsCommand;
use Frolax\Typescript\Contracts\FormatterContract;
use Frolax\Typescript\Contracts\ModelDiscoveryContract;
use Frolax\Typescript\Contracts\ModelMetadataExtractorContract;
use Frolax\Typescript\Contracts\RelationResolverContract;
use Frolax\Typescript\Contracts\TypeResolverContract;
use Frolax\Typescript\Contracts\WriterContract;
use Frolax\Typescript\Discovery\ModelDiscovery;
use Frolax\Typescript\Formatters\BiomeFormatter;
use Frolax\Typescript\Formatters\NullFormatter;
use Frolax\Typescript\Formatters\PrettierFormatter;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Frolax\Typescript\Metadata\ModelMetadataExtractor;
use Frolax\Typescript\Pipeline\GenerationPipeline;
use Frolax\Typescript\Relations\RelationResolver;
use Frolax\Typescript\Resolvers\TypeResolver;
use Frolax\Typescript\Writers\JsonWriter;
use Frolax\Typescript\Writers\TypescriptWriter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Artisan;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TypescriptServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-typescript')
            ->hasConfigFile('typescript')
            ->hasCommands([
                GenerateTypescriptCommand::class,
                InspectModelCommand::class,
                ShowMappingsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Singletons for registries
        $this->app->singleton(SchemaIntrospectorRegistry::class);
        $this->app->singleton(TypeMapperRegistry::class);

        // Bind contracts to concrete implementations
        $this->app->bind(ModelDiscoveryContract::class, ModelDiscovery::class);
        $this->app->bind(ModelMetadataExtractorContract::class, ModelMetadataExtractor::class);
        $this->app->bind(TypeResolverContract::class, function ($app) {
            return new TypeResolver($app->make(TypeMapperRegistry::class));
        });
        $this->app->bind(RelationResolverContract::class, RelationResolver::class);

        // Writer binding (select based on config)
        $this->app->bind(WriterContract::class, function ($app) {
            $writerType = config('typescript.writer.default', 'interface');

            return match ($writerType) {
                'json' => new JsonWriter,
                default => new TypescriptWriter,
            };
        });

        // Formatter
        $this->app->bind(FormatterContract::class, function () {
            $enabled = (bool) config('typescript.formatter.enabled', false);

            if (! $enabled) {
                return new NullFormatter;
            }

            $tool = config('typescript.formatter.tool');
            $binary = config('typescript.formatter.binary');
            $formatterConfig = config('typescript.formatter.config');

            return match ($tool) {
                'prettier' => new PrettierFormatter(
                    binary: is_string($binary) && trim($binary) !== '' ? $binary : 'npx prettier',
                    options: array_filter([
                        '--parser' => 'typescript',
                        '--config' => is_string($formatterConfig) && trim($formatterConfig) !== '' ? $formatterConfig : null,
                    ], fn ($value) => $value !== null)
                ),
                'biome' => new BiomeFormatter(
                    binary: is_string($binary) && trim($binary) !== '' ? $binary : 'npx @biomejs/biome'
                ),
                default => new NullFormatter,
            };
        });

        // Pipeline
        $this->app->bind(GenerationPipeline::class, function ($app) {
            return new GenerationPipeline(
                discovery: $app->make(ModelDiscoveryContract::class),
                introspectorRegistry: $app->make(SchemaIntrospectorRegistry::class),
                metadataExtractor: $app->make(ModelMetadataExtractorContract::class),
                typeResolver: $app->make(TypeResolverContract::class),
                relationResolver: $app->make(RelationResolverContract::class),
                writer: $app->make(WriterContract::class),
                formatter: $app->make(FormatterContract::class),
                events: $app->make(Dispatcher::class),
            );
        });
    }

    public function packageBooted(): void
    {
        // Apply extensions
        $typescript = new Typescript;
        $typescript->applyExtensions(
            $this->app->make(TypeMapperRegistry::class),
            $this->app->make(SchemaIntrospectorRegistry::class),
        );

        // Register event listener for auto-generation after migrations
        if (config('typescript.pipeline.after_migrate', false)) {
            $this->app['events']->listen(
                MigrationsEnded::class,
                function () {
                    Artisan::call('typescript:generate');
                }
            );
        }
    }
}
