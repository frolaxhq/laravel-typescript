<?php

declare(strict_types=1);

namespace Frolax\Typescript\Commands;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Discovery\ModelDiscovery;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Metadata\ModelMetadataExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class InspectModelCommand extends Command
{
    protected $signature = 'typescript:inspect
        {model : The model class name to inspect}
        {--connection= : Database connection to use}
        {--json : Output as JSON}';

    protected $description = 'Inspect a model and show its metadata for TypeScript generation';

    public function handle(
        ModelDiscovery $discovery,
        SchemaIntrospectorRegistry $introspectorRegistry,
        ModelMetadataExtractor $extractor,
    ): int {
        $modelName = $this->argument('model');
        $config = GenerationConfig::fromArray(
            config: Config::get('typescript', []),
            options: [
                'model' => $modelName,
                'connection' => $this->option('connection'),
            ],
        );

        $models = $discovery->discover($config);

        if ($models->isEmpty()) {
            $this->error("Model '{$modelName}' not found.");

            return self::FAILURE;
        }

        $model = $models->first();

        try {
            $introspector = $introspectorRegistry->getForConnection(
                $config->connection ?? $model->connection ?? config('database.default')
            );

            $instance = new ($model->className);
            $rawColumns = $introspector->getColumns($instance);
            $metadata = $extractor->extract($model, $rawColumns);

            if ($this->option('json')) {
                $this->line(json_encode([
                    'model' => $metadata->shortName,
                    'class' => $metadata->className,
                    'table' => $metadata->table,
                    'connection' => $metadata->connection,
                    'primaryKey' => $metadata->primaryKey,
                    'columns' => $metadata->columns->map(fn ($c) => [
                        'name' => $c->name,
                        'dbType' => $c->dbType,
                        'castType' => $c->castType,
                        'nullable' => $c->nullable,
                        'hidden' => $c->hidden,
                        'fillable' => $c->fillable,
                    ])->values()->all(),
                    'accessors' => $metadata->accessors->map(fn ($a) => [
                        'name' => $a->name,
                        'style' => $a->style,
                        'returnType' => $a->returnType,
                        'isNullable' => $a->isNullable,
                    ])->values()->all(),
                    'relations' => $metadata->relations->map(fn ($r) => [
                        'name' => $r->name,
                        'type' => $r->type,
                        'relatedModel' => $r->relatedModel,
                        'isCollection' => $r->isCollection,
                    ])->values()->all(),
                    'casts' => $metadata->casts,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->info("Model: {$metadata->shortName} ({$metadata->className})");
                $this->info("Table: {$metadata->table} (via {$metadata->connection})");
                $this->info("Primary Key: {$metadata->primaryKey} ({$metadata->primaryKeyType})");
                $this->newLine();

                $this->table(
                    ['Column', 'DB Type', 'Cast', 'Nullable', 'Hidden', 'Fillable'],
                    $metadata->columns->map(fn ($c) => [
                        $c->name, $c->dbType, $c->castType ?? '-',
                        $c->nullable ? '✓' : '-',
                        $c->hidden ? '✓' : '-',
                        $c->fillable ? '✓' : '-',
                    ])->all()
                );

                if ($metadata->accessors->isNotEmpty()) {
                    $this->newLine();
                    $this->info('Accessors:');
                    $this->table(
                        ['Name', 'Style', 'Return Type', 'Nullable'],
                        $metadata->accessors->map(fn ($a) => [
                            $a->name, $a->style, $a->returnType ?? 'unknown',
                            $a->isNullable ? '✓' : '-',
                        ])->all()
                    );
                }

                if ($metadata->relations->isNotEmpty()) {
                    $this->newLine();
                    $this->info('Relations:');
                    $this->table(
                        ['Name', 'Type', 'Related', 'Collection'],
                        $metadata->relations->map(fn ($r) => [
                            $r->name, $r->type, $r->relatedShortName,
                            $r->isCollection ? '✓' : '-',
                        ])->all()
                    );
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
