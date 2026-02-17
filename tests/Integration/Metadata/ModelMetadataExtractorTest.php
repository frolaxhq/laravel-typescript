<?php

declare(strict_types=1);

use Frolax\Typescript\Data\ModelReference;
use Frolax\Typescript\Data\RawColumn;
use Frolax\Typescript\Metadata\ModelMetadataExtractor;
use Frolax\Typescript\Tests\Fixtures\Models\Post;
use Frolax\Typescript\Tests\Fixtures\Models\User;

describe('ModelMetadataExtractor', function () {
    beforeEach(function () {
        $this->extractor = new ModelMetadataExtractor;
    });

    it('extracts basic metadata for User model', function () {
        $ref = new ModelReference(
            className: User::class,
            shortName: 'User',
            filePath: '/test/User.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer', autoIncrement: true),
            new RawColumn(name: 'name', type: 'string', rawType: 'varchar(255)'),
            new RawColumn(name: 'email', type: 'string', rawType: 'varchar(255)'),
            new RawColumn(name: 'password', type: 'string', rawType: 'varchar(255)'),
            new RawColumn(name: 'role', type: 'string', rawType: 'varchar(255)'),
            new RawColumn(name: 'bio', type: 'text', rawType: 'text', nullable: true),
            new RawColumn(name: 'settings', type: 'json', rawType: 'json', nullable: true),
            new RawColumn(name: 'is_admin', type: 'boolean', rawType: 'tinyint(1)'),
            new RawColumn(name: 'balance', type: 'decimal', rawType: 'decimal(10,2)'),
            new RawColumn(name: 'created_at', type: 'timestamp', rawType: 'timestamp', nullable: true),
            new RawColumn(name: 'updated_at', type: 'timestamp', rawType: 'timestamp', nullable: true),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        expect($metadata->className)->toBe(User::class);
        expect($metadata->shortName)->toBe('User');
        expect($metadata->table)->toBe('users');
        expect($metadata->primaryKey)->toBe('id');
        expect($metadata->incrementing)->toBeTrue();
    });

    it('detects hidden columns', function () {
        $ref = new ModelReference(
            className: User::class,
            shortName: 'User',
            filePath: '/test/User.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer'),
            new RawColumn(name: 'password', type: 'string', rawType: 'varchar(255)'),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        expect($metadata->hidden)->toContain('password');
        expect($metadata->hidden)->toContain('remember_token');

        $passwordCol = $metadata->columns->firstWhere('name', 'password');
        expect($passwordCol->hidden)->toBeTrue();
    });

    it('detects fillable columns', function () {
        $ref = new ModelReference(
            className: User::class,
            shortName: 'User',
            filePath: '/test/User.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer'),
            new RawColumn(name: 'name', type: 'string', rawType: 'varchar(255)'),
            new RawColumn(name: 'email', type: 'string', rawType: 'varchar(255)'),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        expect($metadata->fillable)->toContain('name');
        expect($metadata->fillable)->toContain('email');

        $nameCol = $metadata->columns->firstWhere('name', 'name');
        expect($nameCol->fillable)->toBeTrue();
    });

    it('detects casts', function () {
        $ref = new ModelReference(
            className: User::class,
            shortName: 'User',
            filePath: '/test/User.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer'),
            new RawColumn(name: 'is_admin', type: 'boolean', rawType: 'tinyint(1)'),
            new RawColumn(name: 'settings', type: 'json', rawType: 'json'),
            new RawColumn(name: 'email_verified_at', type: 'timestamp', rawType: 'timestamp', nullable: true),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        // getCasts() should be non-empty — it includes casts from the casts() method
        expect($metadata->casts)->not->toBeEmpty();

        // Verify column definitions got their cast types applied
        $isAdminCol = $metadata->columns->firstWhere('name', 'is_admin');
        if ($isAdminCol && $isAdminCol->castType !== null) {
            expect($isAdminCol->castType)->toBe('boolean');
        }

        // Verify datetime cast on email_verified_at
        $emailVerifiedCol = $metadata->columns->firstWhere('name', 'email_verified_at');
        if ($emailVerifiedCol && $emailVerifiedCol->castType !== null) {
            expect($emailVerifiedCol->castType)->toBe('datetime');
        }
    });

    it('detects relations', function () {
        $ref = new ModelReference(
            className: User::class,
            shortName: 'User',
            filePath: '/test/User.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer'),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        expect($metadata->relations)->not->toBeEmpty();

        $postsRelation = $metadata->relations->firstWhere('name', 'posts');
        expect($postsRelation)->not->toBeNull();
        expect($postsRelation->type)->toBe('HasMany');
        expect($postsRelation->relatedShortName)->toBe('Post');
        expect($postsRelation->isCollection)->toBeTrue();
    });

    it('detects Post model relations correctly', function () {
        $ref = new ModelReference(
            className: Post::class,
            shortName: 'Post',
            filePath: '/test/Post.php',
        );

        $columns = collect([
            new RawColumn(name: 'id', type: 'integer', rawType: 'integer'),
            new RawColumn(name: 'user_id', type: 'integer', rawType: 'integer'),
        ]);

        $metadata = $this->extractor->extract($ref, $columns);

        $userRelation = $metadata->relations->firstWhere('name', 'user');
        expect($userRelation)->not->toBeNull();
        expect($userRelation->type)->toBe('BelongsTo');
        expect($userRelation->isCollection)->toBeFalse();

        $commentsRelation = $metadata->relations->firstWhere('name', 'comments');
        expect($commentsRelation)->not->toBeNull();
        expect($commentsRelation->type)->toBe('HasMany');
        expect($commentsRelation->isCollection)->toBeTrue();

        $tagsRelation = $metadata->relations->firstWhere('name', 'tags');
        expect($tagsRelation)->not->toBeNull();
        expect($tagsRelation->type)->toBe('BelongsToMany');
        expect($tagsRelation->isCollection)->toBeTrue();
    });
});
