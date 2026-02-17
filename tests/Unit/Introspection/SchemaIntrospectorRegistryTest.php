<?php

declare(strict_types=1);

use Frolax\Typescript\Introspection\FallbackSchemaIntrospector;
use Frolax\Typescript\Introspection\LaravelSchemaIntrospector;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;

describe('SchemaIntrospectorRegistry', function () {
    it('returns LaravelSchemaIntrospector for SQLite', function () {
        $registry = new SchemaIntrospectorRegistry;

        $introspector = $registry->getForConnection('testing');

        expect($introspector)->toBeInstanceOf(LaravelSchemaIntrospector::class);
    });

    it('returns FallbackSchemaIntrospector for unknown drivers', function () {
        $registry = new SchemaIntrospectorRegistry;

        config()->set('database.connections.mongo_conn', [
            'driver' => 'mongodb',
            'database' => 'test',
        ]);

        $introspector = $registry->getForConnection('mongo_conn');

        expect($introspector)->toBeInstanceOf(FallbackSchemaIntrospector::class);
    });

    it('custom introspectors take priority', function () {
        $registry = new SchemaIntrospectorRegistry;

        $customIntrospector = new class extends FallbackSchemaIntrospector
        {
            public function supports(string $driver): bool
            {
                return $driver === 'sqlite';
            }
        };

        $registry->register($customIntrospector);

        $introspector = $registry->getForConnection('testing');

        expect($introspector)->toBe($customIntrospector);
    });
});
