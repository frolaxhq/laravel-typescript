<?php

declare(strict_types=1);

use Frolax\Typescript\Data\EnumDefinition;
use Frolax\Typescript\Data\GenerationResult;
use Frolax\Typescript\Data\ModelGenerationResult;
use Frolax\Typescript\Data\ResolvedRelation;
use Frolax\Typescript\Data\WriterConfig;
use Frolax\Typescript\Writers\TypescriptWriter;
use Frolax\Typescript\Writers\JsonWriter;

describe('TypescriptWriter', function () {
    beforeEach(function () {
        $this->writer = new TypescriptWriter();
    });

    it('generates interface output', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'name', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'email', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'bio', 'tsType' => 'string | null', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig();
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('export interface User {');
        expect($output->stdout)->toContain('  id: number;');
        expect($output->stdout)->toContain('  name: string;');
        expect($output->stdout)->toContain('  email: string;');
        expect($output->stdout)->toContain('  bio: string | null;');
        expect($output->stdout)->toContain('}');
    });

    it('generates type output', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(writer: 'type');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('export type User = {');
        expect($output->stdout)->toContain('};');
    });

    it('generates relations', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect([
                        new ResolvedRelation(name: 'posts', tsType: 'Post[]'),
                        new ResolvedRelation(name: 'profile', tsType: 'Profile'),
                    ]),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig();
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('// Relations');
        expect($output->stdout)->toContain('  posts: Post[];');
        expect($output->stdout)->toContain('  profile: Profile;');
    });

    it('generates counts', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect([
                        new ResolvedRelation(name: 'posts_count', tsType: 'number', optional: true),
                    ]),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig();
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('// Counts');
        expect($output->stdout)->toContain('  posts_count?: number;');
    });

    it('generates const object enum', function () {
        $result = new GenerationResult(
            models: collect(),
            enums: collect([
                new EnumDefinition(
                    className: 'App\\Enums\\Status',
                    shortName: 'Status',
                    backingType: 'string',
                    cases: ['Draft' => 'draft', 'Published' => 'published'],
                ),
            ]),
        );

        $config = new WriterConfig(enumStyle: 'const_object');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain("export const Status = {");
        expect($output->stdout)->toContain("  Draft: 'draft',");
        expect($output->stdout)->toContain("  Published: 'published',");
        expect($output->stdout)->toContain("} as const;");
        expect($output->stdout)->toContain("export type Status = typeof Status[keyof typeof Status];");
    });

    it('generates TypeScript enum', function () {
        $result = new GenerationResult(
            models: collect(),
            enums: collect([
                new EnumDefinition(
                    className: 'App\\Enums\\Status',
                    shortName: 'Status',
                    backingType: 'string',
                    cases: ['Draft' => 'draft', 'Published' => 'published'],
                ),
            ]),
        );

        $config = new WriterConfig(enumStyle: 'ts_enum');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain("export enum Status {");
        expect($output->stdout)->toContain("  Draft = 'draft',");
        expect($output->stdout)->toContain("  Published = 'published',");
    });

    it('generates union type enum', function () {
        $result = new GenerationResult(
            models: collect(),
            enums: collect([
                new EnumDefinition(
                    className: 'App\\Enums\\Status',
                    shortName: 'Status',
                    backingType: 'string',
                    cases: ['Draft' => 'draft', 'Published' => 'published'],
                ),
            ]),
        );

        $config = new WriterConfig(enumStyle: 'union');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain("export type Status = 'draft' | 'published';");
    });

    it('generates pluralized type names', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(plurals: true);
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('export interface Users {');
    });

    it('generates fillable type', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'name', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'email', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                    fillable: ['name', 'email'],
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(fillableTypes: true);
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('export interface UserFillable {');
        expect($output->stdout)->toContain('  name: string;');
        expect($output->stdout)->toContain('  email: string;');
    });

    it('wraps in global namespace', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(globalNamespace: 'models');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('declare namespace models {');
    });

    it('uses column casing', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'first_name', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(columnCase: 'camel');
        $output = $this->writer->write($result, $config);

        expect($output->stdout)->toContain('  firstName: string;');
    });
    it('generates per-model files', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
                new ModelGenerationResult(
                    shortName: 'Post',
                    className: 'App\\Models\\Post',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                        ['name' => 'title', 'tsType' => 'string', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(perModelFiles: true);
        $output = $this->writer->write($result, $config);

        expect($output->files)->toHaveKey('User.ts');
        expect($output->files)->toHaveKey('Post.ts');
        expect($output->files)->toHaveKey('index.ts');

        expect($output->files['User.ts'])->toContain('export interface User {');
        expect($output->files['Post.ts'])->toContain('export interface Post {');
        expect($output->files['Post.ts'])->toContain('  title: string;');
    });

    it('generates barrel exports', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(perModelFiles: true, barrelExport: true);
        $output = $this->writer->write($result, $config);

        expect($output->files)->toHaveKey('index.ts');
        expect($output->files['index.ts'])->toContain("export * from './User';");
    });

    it('generates per-model enum files', function () {
        $result = new GenerationResult(
            models: collect(),
            enums: collect([
                new EnumDefinition(
                    className: 'App\\Enums\\Status',
                    shortName: 'Status',
                    backingType: 'string',
                    cases: ['Draft' => 'draft', 'Published' => 'published'],
                ),
            ]),
        );

        $config = new WriterConfig(perModelFiles: true);
        $output = $this->writer->write($result, $config);

        expect($output->files)->toHaveKey('enums/Status.ts');
        expect($output->files['enums/Status.ts'])->toContain("export const Status = {");
        expect($output->files['index.ts'])->toContain("export * from './enums/Status';");
    });

    it('produces file map in single-file mode', function () {
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(singleFileName: 'types.d.ts');
        $output = $this->writer->write($result, $config);

        expect($output->files)->toHaveKey('types.d.ts');
        expect($output->files['types.d.ts'])->toBe($output->stdout);
    });
});

describe('JsonWriter', function () {
    it('generates JSON output', function () {
        $writer = new JsonWriter();
        $result = new GenerationResult(
            models: collect([
                new ModelGenerationResult(
                    shortName: 'User',
                    className: 'App\\Models\\User',
                    properties: collect([
                        ['name' => 'id', 'tsType' => 'number', 'optional' => false, 'section' => 'columns'],
                    ]),
                    relations: collect(),
                    counts: collect(),
                    exists: collect(),
                    sums: collect(),
                    enums: collect(),
                ),
            ]),
            enums: collect(),
        );

        $config = new WriterConfig(writer: 'json');
        $output = $writer->write($result, $config);

        $json = json_decode($output->stdout, true);
        expect($json)->toHaveKey('models');
        expect($json['models'])->toHaveCount(1);
        expect($json['models'][0]['name'])->toBe('User');
        expect($json['models'][0]['properties'])->toHaveCount(1);
    });
});
