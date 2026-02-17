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
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Frolax\Typescript\Metadata\ModelMetadataExtractor;
use Frolax\Typescript\Pipeline\GenerationPipeline;
use Frolax\Typescript\Relations\RelationResolver;
use Frolax\Typescript\Resolvers\TypeResolver;
use Frolax\Typescript\Writers\JsonWriter;
use Frolax\Typescript\Writers\TypescriptWriter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
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
                'json' => new JsonWriter(),
                default => new TypescriptWriter(),
            };
        });

        // Formatter (null by default)
        $this->app->bind(FormatterContract::class, function () {
            return null; // Will be extended via plugins
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
        $typescript = new Typescript();
        $typescript->applyExtensions(
            $this->app->make(TypeMapperRegistry::class),
            $this->app->make(SchemaIntrospectorRegistry::class),
        );

        // Register event listener for auto-generation after migrations
        if (config('typescript.auto_generate.after_migrate', false)) {
            $this->app['events']->listen(
                \Illuminate\Database\Events\MigrationsEnded::class,
                function () {
                    \Illuminate\Support\Facades\Artisan::call('typescript:generate');
                }
            );
        }
    }
}
