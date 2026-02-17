<?php

declare(strict_types=1);

use Frolax\Typescript\Data\ColumnDefinition;
use Frolax\Typescript\Data\AccessorDefinition;
use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Frolax\Typescript\Resolvers\ResolverContext;
use Frolax\Typescript\Resolvers\TypeResolver;
use Frolax\Typescript\Tests\Fixtures\Enums\PostStatus;
use Frolax\Typescript\Tests\Fixtures\Models\User;

describe('TypeResolver', function () {
    beforeEach(function () {
        $this->registry = new TypeMapperRegistry();
        $this->resolver = new TypeResolver($this->registry);
        $this->config = new GenerationConfig();
        $this->context = new ResolverContext(
            config: $this->config,
            reflectionModel: new ReflectionClass(User::class),
        );
    });

    describe('resolve columns', function () {
        it('resolves string columns', function () {
            $col = new ColumnDefinition(name: 'name', dbType: 'string');
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('string');
            expect($result->nullable)->toBeFalse();
            expect($result->source)->toBe('db_type');
        });

        it('resolves integer columns', function () {
            $col = new ColumnDefinition(name: 'id', dbType: 'integer');
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('number');
        });

        it('resolves boolean columns', function () {
            $col = new ColumnDefinition(name: 'is_admin', dbType: 'boolean');
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('boolean');
        });

        it('resolves json columns', function () {
            $col = new ColumnDefinition(name: 'settings', dbType: 'json');
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('Record<string, unknown>');
        });

        it('resolves nullable columns', function () {
            $col = new ColumnDefinition(name: 'bio', dbType: 'text', nullable: true);
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('string');
            expect($result->nullable)->toBeTrue();
            expect($result->toTypeString())->toBe('string | null');
        });

        it('resolves uuid columns', function () {
            $col = new ColumnDefinition(name: 'uuid', dbType: 'uuid');
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('string');
        });
    });

    describe('forced type override (priority 1)', function () {
        it('uses forced type when set', function () {
            $col = new ColumnDefinition(
                name: 'custom',
                dbType: 'string',
                forcedType: 'CustomType',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('CustomType');
            expect($result->source)->toBe('override');
        });

        it('forced type takes precedence over cast', function () {
            $col = new ColumnDefinition(
                name: 'field',
                dbType: 'string',
                castType: 'integer',
                forcedType: 'MyCustom',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('MyCustom');
            expect($result->source)->toBe('override');
        });
    });

    describe('enum cast resolution (priority 3)', function () {
        it('resolves enum cast types', function () {
            $col = new ColumnDefinition(
                name: 'status',
                dbType: 'string',
                castType: PostStatus::class,
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('PostStatus');
            expect($result->source)->toBe('enum_cast');
            expect($result->enum)->not->toBeNull();
            expect($result->enum->backingType)->toBe('string');
            expect($result->enum->cases)->toHaveCount(3);
        });
    });

    describe('cast type resolution (priority 5)', function () {
        it('resolves integer cast', function () {
            $col = new ColumnDefinition(
                name: 'count',
                dbType: 'string',
                castType: 'integer',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('number');
            expect($result->source)->toBe('cast');
        });

        it('resolves boolean cast', function () {
            $col = new ColumnDefinition(
                name: 'flag',
                dbType: 'integer',
                castType: 'boolean',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('boolean');
            expect($result->source)->toBe('cast');
        });

        it('resolves array cast', function () {
            $col = new ColumnDefinition(
                name: 'data',
                dbType: 'text',
                castType: 'array',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('Record<string, unknown>');
            expect($result->source)->toBe('cast');
        });

        it('resolves datetime cast', function () {
            $col = new ColumnDefinition(
                name: 'published_at',
                dbType: 'string',
                castType: 'datetime',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('string');
            expect($result->source)->toBe('cast');
        });

        it('resolves decimal cast with parameters', function () {
            $col = new ColumnDefinition(
                name: 'balance',
                dbType: 'decimal',
                castType: 'decimal:2',
            );
            $result = $this->resolver->resolve($col, $this->context);

            expect($result->tsType)->toBe('number');
            expect($result->source)->toBe('cast');
        });
    });

    describe('timestamps as Date', function () {
        it('resolves timestamps to Date when config is set', function () {
            $config = new GenerationConfig(timestampsAsDate: true);
            $context = new ResolverContext(
                config: $config,
                reflectionModel: new ReflectionClass(User::class),
            );

            $col = new ColumnDefinition(
                name: 'created_at',
                dbType: 'timestamp',
                isTimestamp: true,
            );
            $result = $this->resolver->resolve($col, $context);

            expect($result->tsType)->toBe('Date');
        });
    });

    describe('custom mappings', function () {
        it('uses custom mappings from config', function () {
            $config = new GenerationConfig(customMappings: ['point' => '{ lat: number; lng: number }']);
            $context = new ResolverContext(
                config: $config,
                reflectionModel: new ReflectionClass(User::class),
            );

            $col = new ColumnDefinition(name: 'location', dbType: 'point');
            $result = $this->resolver->resolve($col, $context);

            expect($result->tsType)->toBe('{ lat: number; lng: number }');
            expect($result->source)->toBe('custom_mapping');
        });
    });

    describe('accessor resolution', function () {
        it('resolves accessor with return type', function () {
            $accessor = new AccessorDefinition(
                name: 'full_name',
                style: 'attribute',
                returnType: 'string',
            );
            $result = $this->resolver->resolveAccessor($accessor, $this->context);

            expect($result->tsType)->toBe('string');
            expect($result->source)->toBe('accessor');
        });

        it('resolves nullable accessor', function () {
            $accessor = new AccessorDefinition(
                name: 'middle_name',
                style: 'attribute',
                returnType: 'string',
                isNullable: true,
            );
            $result = $this->resolver->resolveAccessor($accessor, $this->context);

            expect($result->tsType)->toBe('string');
            expect($result->nullable)->toBeTrue();
        });

        it('resolves accessor with enum return type', function () {
            $accessor = new AccessorDefinition(
                name: 'status',
                style: 'attribute',
                returnType: PostStatus::class,
                enumClass: PostStatus::class,
            );
            $result = $this->resolver->resolveAccessor($accessor, $this->context);

            expect($result->tsType)->toBe('PostStatus');
            expect($result->enum)->not->toBeNull();
        });

        it('resolves accessor with no return type as unknown', function () {
            $accessor = new AccessorDefinition(
                name: 'calculated',
                style: 'traditional',
            );
            $result = $this->resolver->resolveAccessor($accessor, $this->context);

            expect($result->tsType)->toBe('unknown');
        });
    });
});
