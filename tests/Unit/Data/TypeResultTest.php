<?php

declare(strict_types=1);

use Frolax\Typescript\Data\TypeResult;

describe('TypeResult', function () {
    it('creates a simple type result', function () {
        $result = new TypeResult(tsType: 'string', source: 'db_type');

        expect($result->tsType)->toBe('string');
        expect($result->nullable)->toBeFalse();
        expect($result->optional)->toBeFalse();
        expect($result->source)->toBe('db_type');
        expect($result->toTypeString())->toBe('string');
    });

    it('handles nullable types', function () {
        $result = new TypeResult(tsType: 'string', nullable: true, source: 'db_type');

        expect($result->toTypeString())->toBe('string | null');
    });

    it('creates unknown type via factory', function () {
        $result = TypeResult::unknown();

        expect($result->tsType)->toBe('unknown');
        expect($result->source)->toBe('unknown');
    });

    it('creates simple type via factory', function () {
        $result = TypeResult::simple('number', 'cast');

        expect($result->tsType)->toBe('number');
        expect($result->source)->toBe('cast');
    });

    it('creates array type via factory', function () {
        $result = TypeResult::array('Status', 'cast');

        expect($result->tsType)->toBe('Status[]');
        expect($result->source)->toBe('cast');
    });

    it('creates circular reference', function () {
        $result = TypeResult::circularReference('User');

        expect($result->tsType)->toBe('User');
        expect($result->source)->toBe('circular_ref');
    });

    it('creates shallow reference', function () {
        $result = TypeResult::shallowReference('Post');

        expect($result->tsType)->toBe('Post');
        expect($result->source)->toBe('shallow_ref');
    });
});
