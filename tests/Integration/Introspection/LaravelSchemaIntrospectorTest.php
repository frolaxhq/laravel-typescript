<?php

declare(strict_types=1);

use Frolax\Typescript\Introspection\LaravelSchemaIntrospector;
use Frolax\Typescript\Tests\Fixtures\Models\Post;
use Frolax\Typescript\Tests\Fixtures\Models\User;

describe('LaravelSchemaIntrospector', function () {
    beforeEach(function () {
        $this->introspector = new LaravelSchemaIntrospector;
    });

    it('supports common database drivers', function () {
        expect($this->introspector->supports('mysql'))->toBeTrue();
        expect($this->introspector->supports('pgsql'))->toBeTrue();
        expect($this->introspector->supports('sqlite'))->toBeTrue();
        expect($this->introspector->supports('sqlsrv'))->toBeTrue();
        expect($this->introspector->supports('mongodb'))->toBeFalse();
    });

    it('gets columns for User model', function () {
        $user = new User;
        $columns = $this->introspector->getColumns($user);

        expect($columns)->not->toBeEmpty();

        $columnNames = $columns->pluck('name')->toArray();
        expect($columnNames)->toContain('id');
        expect($columnNames)->toContain('name');
        expect($columnNames)->toContain('email');
        expect($columnNames)->toContain('password');
        expect($columnNames)->toContain('bio');
        expect($columnNames)->toContain('settings');
        expect($columnNames)->toContain('is_admin');
        expect($columnNames)->toContain('created_at');
        expect($columnNames)->toContain('updated_at');
    });

    it('detects nullable columns', function () {
        $user = new User;
        $columns = $this->introspector->getColumns($user);

        $bioColumn = $columns->firstWhere('name', 'bio');
        expect($bioColumn->nullable)->toBeTrue();

        $nameColumn = $columns->firstWhere('name', 'name');
        expect($nameColumn->nullable)->toBeFalse();
    });

    it('normalizes column types correctly', function () {
        $user = new User;
        $columns = $this->introspector->getColumns($user);

        $idColumn = $columns->firstWhere('name', 'id');
        expect($idColumn->type)->toBe('integer');

        $nameColumn = $columns->firstWhere('name', 'name');
        expect($nameColumn->type)->toBeIn(['string', 'varchar']);

        $settingsColumn = $columns->firstWhere('name', 'settings');
        // SQLite stores json as text
        expect($settingsColumn->type)->toBeIn(['json', 'text']);
    });

    it('gets columns for Post model', function () {
        $post = new Post;
        $columns = $this->introspector->getColumns($post);

        $columnNames = $columns->pluck('name')->toArray();
        expect($columnNames)->toContain('id');
        expect($columnNames)->toContain('user_id');
        expect($columnNames)->toContain('title');
        expect($columnNames)->toContain('body');
        expect($columnNames)->toContain('status');
    });

    it('gets column type by name', function () {
        $user = new User;
        $type = $this->introspector->getColumnType($user, 'name');

        expect($type)->toBeIn(['string', 'varchar']);
    });

    it('returns string for unknown columns', function () {
        $user = new User;
        $type = $this->introspector->getColumnType($user, 'nonexistent_column');

        expect($type)->toBe('string');
    });
});
