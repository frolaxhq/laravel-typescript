<?php

declare(strict_types=1);

describe('GenerateTypescriptCommand', function () {
    it('outputs TypeScript to stdout', function () {
        config()->set('typescript.discovery.paths', [
            __DIR__.'/../../Fixtures/Models',
        ]);

        $this->artisan('typescript:generate', ['--stdout' => true])
            ->assertSuccessful();
    });

    it('generates only specified model', function () {
        config()->set('typescript.discovery.paths', [
            __DIR__.'/../../Fixtures/Models',
        ]);

        $this->artisan('typescript:generate', [
            'model' => 'User',
            '--stdout' => true,
        ])->assertSuccessful();
    });

    it('accepts writer option', function () {
        config()->set('typescript.discovery.paths', [
            __DIR__.'/../../Fixtures/Models',
        ]);

        $this->artisan('typescript:generate', [
            '--writer' => 'type',
            '--stdout' => true,
        ])->assertSuccessful();
    });

    it('fails when no models found', function () {
        config()->set('typescript.discovery.paths', [
            '/tmp/nonexistent_path',
        ]);

        $this->artisan('typescript:generate', ['--stdout' => true])
            ->assertFailed();
    });
});

describe('InspectModelCommand', function () {
    it('inspects a model', function () {
        config()->set('typescript.discovery.paths', [
            __DIR__.'/../../Fixtures/Models',
        ]);

        $this->artisan('typescript:inspect', [
            'model' => 'Frolax\\Typescript\\Tests\\Fixtures\\Models\\User',
        ])->assertSuccessful();
    });

    it('inspects model in JSON format', function () {
        config()->set('typescript.discovery.paths', [
            __DIR__.'/../../Fixtures/Models',
        ]);

        $this->artisan('typescript:inspect', [
            'model' => 'Frolax\\Typescript\\Tests\\Fixtures\\Models\\User',
            '--json' => true,
        ])->assertSuccessful();
    });
});

describe('ShowMappingsCommand', function () {
    it('shows default mappings', function () {
        $this->artisan('typescript:mappings')
            ->assertSuccessful();
    });
});
