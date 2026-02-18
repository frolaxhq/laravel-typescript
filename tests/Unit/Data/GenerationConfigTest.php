<?php

declare(strict_types=1);

use Frolax\Typescript\Data\GenerationConfig;

describe('GenerationConfig', function () {
    it('creates with sensible defaults', function () {
        $config = new GenerationConfig;

        expect($config->writer)->toBe('interface');
        expect($config->enumStyle)->toBe('const_object');
        expect($config->relationsEnabled)->toBeTrue();
        expect($config->countsEnabled)->toBeTrue();
        expect($config->existsEnabled)->toBeTrue();
        expect($config->sumsEnabled)->toBeTrue();
        expect($config->includeHidden)->toBeTrue();
        expect($config->columnCase)->toBe('snake');
        expect($config->relationCase)->toBe('snake');
        expect($config->timestampsAsDate)->toBeFalse();
        expect($config->optionalNullables)->toBeFalse();
        expect($config->bailOnError)->toBeFalse();
        expect($config->formatterEnabled)->toBeFalse();
    });

    it('creates from array with config values', function () {
        $config = GenerationConfig::fromArray([
            'writer' => ['default' => 'type'],
            'relations' => ['optional' => true],
            'mappings' => ['timestamps_as_date' => true],
            'case' => ['columns' => 'camel'],
        ]);

        expect($config->writer)->toBe('type');
        expect($config->optionalRelations)->toBeTrue();
        expect($config->timestampsAsDate)->toBeTrue();
        expect($config->columnCase)->toBe('camel');
    });

    it('CLI options override config values', function () {
        $config = GenerationConfig::fromArray(
            config: ['writer' => ['default' => 'interface']],
            options: ['writer' => 'json'],
        );

        expect($config->writer)->toBe('json');
    });

    it('handles boolean CLI flags', function () {
        $config = GenerationConfig::fromArray(
            config: [],
            options: [
                'no-relations' => true,
                'optional-counts' => true,
                'no-hidden' => true,
                'timestamps-as-date' => true,
            ],
        );

        expect($config->relationsEnabled)->toBeFalse();
        expect($config->optionalCounts)->toBeTrue();
        expect($config->includeHidden)->toBeFalse();
        expect($config->timestampsAsDate)->toBeTrue();
        expect($config->relationsEnabled)->toBeFalse();
        expect($config->optionalCounts)->toBeTrue();
        expect($config->includeHidden)->toBeFalse();
        expect($config->timestampsAsDate)->toBeTrue();
    });

    it('handles auto-discover option', function () {
        $config = GenerationConfig::fromArray(
            config: [],
            options: ['auto-discover' => false],
        );

        expect($config->autoDiscover)->toBeFalse();
    });

    it('handles specific model option', function () {
        $config = GenerationConfig::fromArray(
            config: [],
            options: ['model' => 'User'],
        );

        expect($config->specificModel)->toBe('User');
    });

    it('handles custom mappings', function () {
        $config = GenerationConfig::fromArray([
            'mappings' => [
                'custom' => ['point' => '{ lat: number; lng: number }'],
            ],
        ]);

        expect($config->customMappings)->toHaveKey('point');
        expect($config->customMappings['point'])->toBe('{ lat: number; lng: number }');
    });
});
