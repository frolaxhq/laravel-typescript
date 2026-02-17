<?php

declare(strict_types=1);

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\WriterConfig;
use Frolax\Typescript\Discovery\ModelDiscovery;
use Frolax\Typescript\Formatters\NullFormatter;
use Frolax\Typescript\Introspection\SchemaIntrospectorRegistry;
use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Frolax\Typescript\Metadata\ModelMetadataExtractor;
use Frolax\Typescript\Pipeline\GenerationPipeline;
use Frolax\Typescript\Relations\RelationResolver;
use Frolax\Typescript\Resolvers\TypeResolver;
use Frolax\Typescript\Writers\TypescriptWriter;
use Illuminate\Contracts\Events\Dispatcher;

describe('GenerationPipeline — E2E', function () {
    beforeEach(function () {
        $this->pipeline = new GenerationPipeline(
            discovery: new ModelDiscovery,
            introspectorRegistry: new SchemaIntrospectorRegistry,
            metadataExtractor: new ModelMetadataExtractor,
            typeResolver: new TypeResolver(new TypeMapperRegistry),
            relationResolver: new RelationResolver,
            writer: new TypescriptWriter,
            formatter: new NullFormatter,
            events: app(Dispatcher::class),
        );
    });

    it('generates TypeScript from fixture models', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            writer: 'interface',
        );

        $result = $this->pipeline->generate($config);

        expect($result->models)->not->toBeEmpty();
        expect($result->models->count())->toBeGreaterThanOrEqual(4);

        // Verify models are found
        $modelNames = $result->models->pluck('shortName')->toArray();
        expect($modelNames)->toContain('User');
        expect($modelNames)->toContain('Post');
        expect($modelNames)->toContain('Comment');
        expect($modelNames)->toContain('Tag');
    });

    it('generates correct properties for User model', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'User',
            writer: 'interface',
        );

        $result = $this->pipeline->generate($config);

        expect($result->models)->toHaveCount(1);

        $user = $result->models->first();
        expect($user->shortName)->toBe('User');

        $propertyNames = $user->properties->pluck('name')->toArray();
        expect($propertyNames)->toContain('id');
        expect($propertyNames)->toContain('name');
        expect($propertyNames)->toContain('email');
    });

    it('resolves relations for Post model', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'Post',
            relationsEnabled: true,
        );

        $result = $this->pipeline->generate($config);

        $post = $result->models->first();
        expect($post->relations)->not->toBeEmpty();

        $relationNames = $post->relations->pluck('name')->toArray();
        expect($relationNames)->toContain('user');
        expect($relationNames)->toContain('comments');
        expect($relationNames)->toContain('tags');

        // BelongsTo is singular
        $userRel = $post->relations->firstWhere('name', 'user');
        expect($userRel->tsType)->toBe('User');

        // HasMany is array
        $commentsRel = $post->relations->firstWhere('name', 'comments');
        expect($commentsRel->tsType)->toBe('Comment[]');

        // BelongsToMany is array
        $tagsRel = $post->relations->firstWhere('name', 'tags');
        expect($tagsRel->tsType)->toBe('Tag[]');
    });

    it('generates counts for countable relations', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'Post',
            countsEnabled: true,
        );

        $result = $this->pipeline->generate($config);

        $post = $result->models->first();
        expect($post->counts)->not->toBeEmpty();

        $countNames = $post->counts->pluck('name')->toArray();
        expect($countNames)->toContain('comments_count');
        expect($countNames)->toContain('tags_count');
    });

    it('generates exists for relations', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'Post',
            existsEnabled: true,
        );

        $result = $this->pipeline->generate($config);

        $post = $result->models->first();
        expect($post->exists)->not->toBeEmpty();

        $existNames = $post->exists->pluck('name')->toArray();
        expect($existNames)->toContain('user_exists');
        expect($existNames)->toContain('comments_exists');
    });

    it('excludes relations when disabled', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'Post',
            relationsEnabled: false,
        );

        $result = $this->pipeline->generate($config);

        $post = $result->models->first();
        expect($post->relations)->toBeEmpty();
    });

    it('generates TypeScript output string', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'User',
            writer: 'interface',
        );

        $result = $this->pipeline->generate($config);

        $writerConfig = WriterConfig::fromGenerationConfig($config);
        $writer = new TypescriptWriter;
        $output = $writer->write($result, $writerConfig);

        expect($output->stdout)->toContain('export interface User {');
        expect($output->stdout)->toContain('id: number;');
        expect($output->stdout)->toContain('name: string;');
        expect($output->stdout)->toContain('}');
    });

    it('generates type alias output', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            specificModel: 'User',
            writer: 'type',
        );

        $result = $this->pipeline->generate($config);

        $writerConfig = WriterConfig::fromGenerationConfig($config);
        $writer = new TypescriptWriter;
        $output = $writer->write($result, $writerConfig);

        expect($output->stdout)->toContain('export type User = {');
        expect($output->stdout)->toContain('};');
    });

    it('respects model exclusions', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            excludedModels: ['Tag', 'Comment'],
        );

        $result = $this->pipeline->generate($config);

        $modelNames = $result->models->pluck('shortName')->toArray();
        expect($modelNames)->not->toContain('Tag');
        expect($modelNames)->not->toContain('Comment');
        expect($modelNames)->toContain('User');
        expect($modelNames)->toContain('Post');
    });

    it('respects model inclusions', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            includedModels: ['User'],
        );

        $result = $this->pipeline->generate($config);

        expect($result->models)->toHaveCount(1);
        expect($result->models->first()->shortName)->toBe('User');
    });

    it('throws when no models found', function () {
        $config = new GenerationConfig(
            paths: ['/tmp/nonexistent_path'],
        );

        $this->pipeline->generate($config);
    })->throws(\Frolax\Typescript\Exceptions\NoModelsFoundException::class);

    it('generates per-model files with barrel export', function () {
        $config = new GenerationConfig(
            paths: [__DIR__.'/../../Fixtures/Models'],
            perModelFiles: true,
        );

        $result = $this->pipeline->generate($config);

        $writerConfig = WriterConfig::fromGenerationConfig($config);
        $writer = new TypescriptWriter;
        $output = $writer->write($result, $writerConfig);

        expect($output->files)->toHaveKey('User.ts');
        expect($output->files)->toHaveKey('Post.ts');
        expect($output->files)->toHaveKey('Comment.ts');
        expect($output->files)->toHaveKey('Tag.ts');
        expect($output->files)->toHaveKey('index.ts');

        $index = $output->files['index.ts'];
        expect($index)->toContain("export * from './User';");
        expect($index)->toContain("export * from './Post';");
    });
});
