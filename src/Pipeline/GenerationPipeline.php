<?php

declare(strict_types=1);

namespace Frolax\Typescript\Pipeline;

use Frolax\Typescript\Contracts\FormatterContract;
use Frolax\Typescript\Contracts\ModelDiscoveryContract;
use Frolax\Typescript\Contracts\ModelMetadataExtractorContract;
use Frolax\Typescript\Contracts\RelationResolverContract;
use Frolax\Typescript\Contracts\SchemaIntrospectorContract;
use Frolax\Typescript\Contracts\TypeResolverContract;
use Frolax\Typescript\Contracts\WriterContract;
use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\GenerationResult;
use Frolax\Typescript\Data\ModelGenerationResult;
use Frolax\Typescript\Data\ModelReference;
use Frolax\Typescript\Data\WriterConfig;
use Frolax\Typescript\Events\AfterDiscover;
use Frolax\Typescript\Events\AfterResolveTypes;
use Frolax\Typescript\Events\AfterWrite;
use Frolax\Typescript\Events\BeforeDiscover;
use Frolax\Typescript\Events\BeforeResolveTypes;
use Frolax\Typescript\Events\BeforeWrite;
use Frolax\Typescript\Exceptions\NoModelsFoundException;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Resolvers\ResolverContext;
use Frolax\Typescript\Writers\WriterOutput;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

class GenerationPipeline
{
    /** @var list<string> */
    private array $warnings = [];

    public function __construct(
        private readonly ModelDiscoveryContract $discovery,
        private readonly SchemaIntrospectorRegistry $introspectorRegistry,
        private readonly ModelMetadataExtractorContract $metadataExtractor,
        private readonly TypeResolverContract $typeResolver,
        private readonly RelationResolverContract $relationResolver,
        private readonly WriterContract $writer,
        private readonly ?FormatterContract $formatter,
        private readonly Dispatcher $events,
    ) {}

    /**
     * Run the full generation pipeline.
     *
     * @throws NoModelsFoundException
     */
    public function generate(GenerationConfig $config): GenerationResult
    {
        $this->warnings = [];

        // Phase 1: Discovery
        $this->events->dispatch(new BeforeDiscover($config));
        $models = $this->discovery->discover($config);
        $this->events->dispatch(new AfterDiscover($models, $config));

        if ($models->isEmpty()) {
            throw new NoModelsFoundException(
                'No models found in configured paths. Check typescript.discovery.paths config.'
            );
        }

        // Phase 2-5: Process each model
        $modelResults = $models->map(function (ModelReference $model) use ($config) {
            return $this->processModel($model, $config);
        })->filter();

        // Collect all unique enums
        $allEnums = $modelResults
            ->flatMap(fn (ModelGenerationResult $r) => $r->enums)
            ->unique(fn ($enum) => $enum->className)
            ->values();

        $result = new GenerationResult(
            models: $modelResults->values(),
            enums: $allEnums,
            warnings: $this->warnings,
        );

        // Phase 6: Write
        $this->events->dispatch(new BeforeWrite($result, $config));
        $writerConfig = WriterConfig::fromGenerationConfig($config);
        $output = $this->writer->write($result, $writerConfig);
        $this->events->dispatch(new AfterWrite($output, $config));

        // Phase 7: Format (optional)
        if ($this->formatter && $config->formatterEnabled && $this->formatter->isAvailable()) {
            $output = $this->formatOutput($output);
        }

        return new GenerationResult(
            models: $result->models,
            enums: $result->enums,
            warnings: $result->warnings,
            imports: $result->imports,
            output: $output,
        );
    }

    /**
     * Process a single model through the pipeline.
     */
    private function processModel(ModelReference $model, GenerationConfig $config): ?ModelGenerationResult
    {
        try {
            // Get schema introspector for this model's connection
            $introspector = $this->introspectorRegistry->getForConnection(
                $config->connection ?? $model->connection ?? config('database.default')
            );

            // Instantiate model to read schema
            /** @var \Illuminate\Database\Eloquent\Model $instance */
            $instance = new ($model->className);

            // Get raw columns
            $rawColumns = $introspector->getColumns($instance);

            // Extract metadata
            $metadata = $this->metadataExtractor->extract($model, $rawColumns);

            // Fire before resolve
            $this->events->dispatch(new BeforeResolveTypes($metadata, $config));

            // Build resolver context
            $context = new ResolverContext(
                config: $config,
                reflectionModel: new \ReflectionClass($model->className),
            );

            // Resolve types for each column
            $properties = $metadata->columns->map(function ($column) use ($context, $config) {
                $typeResult = $this->typeResolver->resolve($column, $context);

                return [
                    'name' => $column->name,
                    'tsType' => $typeResult->toTypeString(),
                    'optional' => $typeResult->optional || ($column->hidden && $config->includeHidden) || ($config->optionalNullables && $column->nullable),
                    'section' => $column->isAccessor ? 'mutators' : 'columns',
                    'enum' => $typeResult->enum,
                ];
            });

            // Resolve accessors
            $accessorProperties = $metadata->accessors->map(function ($accessor) use ($context) {
                $typeResult = $this->typeResolver->resolveAccessor($accessor, $context);

                return [
                    'name' => $accessor->name,
                    'tsType' => $typeResult->toTypeString(),
                    'optional' => $typeResult->optional,
                    'section' => 'mutators',
                    'enum' => $typeResult->enum,
                ];
            });

            $allProperties = $properties->merge($accessorProperties);

            // Collect enums from resolved types
            $enums = $allProperties
                ->pluck('enum')
                ->filter()
                ->unique(fn ($e) => $e->className)
                ->values();

            // Resolve relations
            $resolvedRelations = $config->relationsEnabled
                ? $this->relationResolver->resolveAll($metadata->relations, $metadata->shortName, $config)
                : collect();

            // Fire after resolve
            $this->events->dispatch(new AfterResolveTypes($metadata, $allProperties, $config));

            return new ModelGenerationResult(
                shortName: $metadata->shortName,
                className: $metadata->className,
                properties: $allProperties->map(fn ($p) => [
                    'name' => $p['name'],
                    'tsType' => $p['tsType'],
                    'optional' => $p['optional'],
                    'section' => $p['section'],
                ]),
                relations: $resolvedRelations,
                counts: $this->buildCounts($metadata, $config),
                exists: $this->buildExists($metadata, $config),
                sums: $this->buildSums($metadata, $config),
                enums: $enums,
                fillable: $metadata->fillable,
                warnings: [],
            );
        } catch (\Throwable $e) {
            $warning = "Failed to process model {$model->shortName}: {$e->getMessage()}. Skipping.";
            $this->warnings[] = $warning;

            if ($config->bailOnError) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Build count properties from relations.
     *
     * @return Collection<int, ResolvedRelation>
     */
    private function buildCounts($metadata, GenerationConfig $config): Collection
    {
        if (! $config->countsEnabled) {
            return collect();
        }

        $countableTypes = [
            'BelongsToMany', 'HasMany', 'HasManyThrough',
            'MorphToMany', 'MorphMany', 'MorphedByMany',
        ];

        return $metadata->relations
            ->filter(fn ($rel) => in_array($rel->type, $countableTypes))
            ->map(fn ($rel) => new \Frolax\Typescript\Data\ResolvedRelation(
                name: $rel->name . '_count',
                tsType: 'number',
                optional: $config->optionalCounts,
            ))
            ->values();
    }

    /**
     * Build exists properties from relations.
     *
     * @return Collection<int, ResolvedRelation>
     */
    private function buildExists($metadata, GenerationConfig $config): Collection
    {
        if (! $config->existsEnabled) {
            return collect();
        }

        $existableTypes = [
            'HasOne', 'HasMany', 'HasOneThrough', 'HasManyThrough',
            'BelongsTo', 'BelongsToMany',
            'MorphOne', 'MorphMany', 'MorphToMany',
        ];

        return $metadata->relations
            ->filter(fn ($rel) => in_array($rel->type, $existableTypes))
            ->map(fn ($rel) => new \Frolax\Typescript\Data\ResolvedRelation(
                name: $rel->name . '_exists',
                tsType: 'boolean',
                optional: $config->optionalExists,
            ))
            ->values();
    }

    /**
     * Build sum properties from model's $sums definition.
     *
     * @return Collection<int, ResolvedRelation>
     */
    private function buildSums($metadata, GenerationConfig $config): Collection
    {
        if (! $config->sumsEnabled || empty($metadata->sumDefinitions)) {
            return collect();
        }

        return collect($metadata->sumDefinitions)
            ->map(fn ($column, $relation) => new \Frolax\Typescript\Data\ResolvedRelation(
                name: "{$relation}_sum_{$column}",
                tsType: 'number | null',
                optional: $config->optionalSums,
            ))
            ->values();
    }

    /**
     * Format writer output using the configured formatter.
     */
    private function formatOutput(WriterOutput $output): WriterOutput
    {
        $formattedFiles = [];
        foreach ($output->files as $path => $content) {
            $formattedFiles[$path] = $this->formatter->format($content, $path);
        }

        return new WriterOutput(
            files: $formattedFiles,
            stdout: $output->stdout ? $this->formatter->format($output->stdout, 'stdout.ts') : null,
        );
    }
}
