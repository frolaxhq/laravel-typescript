<?php

declare(strict_types=1);

namespace Frolax\Typescript\Writers;

use Frolax\Typescript\Contracts\WriterContract;
use Frolax\Typescript\Data\GenerationResult;
use Frolax\Typescript\Data\WriterConfig;

/**
 * Writer that outputs in JSON format for integration with other tools.
 */
class JsonWriter implements WriterContract
{
    public function name(): string
    {
        return 'json';
    }

    public function write(GenerationResult $result, WriterConfig $config): WriterOutput
    {
        $output = [
            'models' => $result->models->map(function ($model) use ($config) {
                return [
                    'name' => $model->shortName,
                    'class' => $model->className,
                    'properties' => $model->properties->map(fn ($p) => [
                        'name' => $p['name'],
                        'type' => $p['tsType'],
                        'optional' => $p['optional'],
                        'section' => $p['section'],
                    ])->values()->all(),
                    'relations' => $model->relations->map(fn ($r) => [
                        'name' => $r->name,
                        'type' => $r->tsType,
                        'optional' => $r->optional,
                        'circular' => $r->isCircular,
                    ])->values()->all(),
                    'counts' => $model->counts->map(fn ($c) => [
                        'name' => $c->name,
                        'type' => $c->tsType,
                    ])->values()->all(),
                    'fillable' => $model->fillable,
                ];
            })->values()->all(),
            'enums' => $result->enums->map(fn ($e) => [
                'name' => $e->shortName,
                'class' => $e->className,
                'backing_type' => $e->backingType,
                'cases' => $e->cases,
            ])->values()->all(),
            'warnings' => $result->warnings,
        ];

        return new WriterOutput(
            stdout: json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}
