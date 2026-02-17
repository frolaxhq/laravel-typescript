<?php

declare(strict_types=1);

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Data\RelationDefinition;
use Frolax\Typescript\Data\ResolvedRelation;
use Frolax\Typescript\Relations\RelationResolver;

describe('RelationResolver', function () {
    beforeEach(function () {
        $this->resolver = new RelationResolver();
    });

    it('resolves HasMany as array type', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'posts',
                type: 'HasMany',
                relatedModel: 'App\\Models\\Post',
                relatedShortName: 'Post',
                isCollection: true,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'User', $config);

        expect($resolved)->toHaveCount(1);
        expect($resolved[0]->tsType)->toBe('Post[]');
    });

    it('resolves BelongsTo as singular type', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'user',
                type: 'BelongsTo',
                relatedModel: 'App\\Models\\User',
                relatedShortName: 'User',
                isCollection: false,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'Post', $config);

        expect($resolved[0]->tsType)->toBe('User');
    });

    it('resolves BelongsToMany as array type', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'tags',
                type: 'BelongsToMany',
                relatedModel: 'App\\Models\\Tag',
                relatedShortName: 'Tag',
                isCollection: true,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'Post', $config);

        expect($resolved[0]->tsType)->toBe('Tag[]');
    });

    it('detects nullable relation', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'parent',
                type: 'BelongsTo',
                relatedModel: 'App\\Models\\Category',
                relatedShortName: 'Category',
                nullable: true,
                isCollection: false,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'Category', $config);

        expect($resolved[0]->tsType)->toBe('Category | null');
    });

    it('detects circular reference', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'parent',
                type: 'BelongsTo',
                relatedModel: 'App\\Models\\User',
                relatedShortName: 'User',
                isCollection: false,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'User', $config);

        expect($resolved[0]->isCircular)->toBeTrue();
    });

    it('detects naming collision with model', function () {
        $relations = collect([
            new RelationDefinition(
                name: 'User',
                type: 'BelongsTo',
                relatedModel: 'App\\Models\\User',
                relatedShortName: 'User',
                isCollection: false,
            ),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'User', $config);

        expect($resolved[0]->warning)->not->toBeNull();
    });

    it('resolves multiple relations', function () {
        $relations = collect([
            new RelationDefinition(name: 'posts', type: 'HasMany', relatedModel: 'App\\Models\\Post', relatedShortName: 'Post', isCollection: true),
            new RelationDefinition(name: 'comments', type: 'HasMany', relatedModel: 'App\\Models\\Comment', relatedShortName: 'Comment', isCollection: true),
            new RelationDefinition(name: 'profile', type: 'HasOne', relatedModel: 'App\\Models\\Profile', relatedShortName: 'Profile', isCollection: false),
        ]);

        $config = new GenerationConfig();
        $resolved = $this->resolver->resolveAll($relations, 'User', $config);

        expect($resolved)->toHaveCount(3);
        expect($resolved[0]->tsType)->toBe('Post[]');
        expect($resolved[1]->tsType)->toBe('Comment[]');
        expect($resolved[2]->tsType)->toBe('Profile');
    });
});
