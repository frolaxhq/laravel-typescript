<?php

declare(strict_types=1);

use Frolax\Typescript\Mappers\DefaultTypeMapper;

describe('DefaultTypeMapper', function () {
    beforeEach(function () {
        $this->mapper = new DefaultTypeMapper;
    });

    it('supports string types', function () {
        expect($this->mapper->supports('string'))->toBeTrue();
        expect($this->mapper->supports('text'))->toBeTrue();
        expect($this->mapper->supports('varchar'))->toBeTrue();
    });

    it('supports numeric types', function () {
        expect($this->mapper->supports('integer'))->toBeTrue();
        expect($this->mapper->supports('bigint'))->toBeTrue();
        expect($this->mapper->supports('float'))->toBeTrue();
        expect($this->mapper->supports('double'))->toBeTrue();
        expect($this->mapper->supports('decimal'))->toBeTrue();
    });

    it('supports boolean types', function () {
        expect($this->mapper->supports('boolean'))->toBeTrue();
        expect($this->mapper->supports('bool'))->toBeTrue();
    });

    it('supports datetime types', function () {
        expect($this->mapper->supports('date'))->toBeTrue();
        expect($this->mapper->supports('datetime'))->toBeTrue();
        expect($this->mapper->supports('timestamp'))->toBeTrue();
    });

    it('supports special types', function () {
        expect($this->mapper->supports('uuid'))->toBeTrue();
        expect($this->mapper->supports('ulid'))->toBeTrue();
        expect($this->mapper->supports('json'))->toBeTrue();
    });

    it('does not support unknown types', function () {
        expect($this->mapper->supports('foobar'))->toBeFalse();
        expect($this->mapper->supports('custom_type'))->toBeFalse();
    });

    it('resolves string types to string', function () {
        expect($this->mapper->resolve('string'))->toBe('string');
        expect($this->mapper->resolve('text'))->toBe('string');
        expect($this->mapper->resolve('varchar'))->toBe('string');
        expect($this->mapper->resolve('char'))->toBe('string');
    });

    it('resolves numeric types to number', function () {
        expect($this->mapper->resolve('integer'))->toBe('number');
        expect($this->mapper->resolve('int'))->toBe('number');
        expect($this->mapper->resolve('bigint'))->toBe('number');
        expect($this->mapper->resolve('float'))->toBe('number');
        expect($this->mapper->resolve('double'))->toBe('number');
        expect($this->mapper->resolve('decimal'))->toBe('number');
        expect($this->mapper->resolve('year'))->toBe('number');
    });

    it('resolves boolean types to boolean', function () {
        expect($this->mapper->resolve('boolean'))->toBe('boolean');
        expect($this->mapper->resolve('bool'))->toBe('boolean');
    });

    it('resolves datetime types to string', function () {
        expect($this->mapper->resolve('date'))->toBe('string');
        expect($this->mapper->resolve('datetime'))->toBe('string');
        expect($this->mapper->resolve('timestamp'))->toBe('string');
        expect($this->mapper->resolve('time'))->toBe('string');
    });

    it('resolves uuid and ulid to string', function () {
        expect($this->mapper->resolve('uuid'))->toBe('string');
        expect($this->mapper->resolve('ulid'))->toBe('string');
    });

    it('resolves json to Record', function () {
        expect($this->mapper->resolve('json'))->toBe('Record<string, unknown>');
    });

    it('resolves binary to Blob', function () {
        expect($this->mapper->resolve('binary'))->toBe('Blob');
    });

    it('is case insensitive', function () {
        expect($this->mapper->supports('STRING'))->toBeTrue();
        expect($this->mapper->supports('Integer'))->toBeTrue();
        expect($this->mapper->resolve('STRING'))->toBe('string');
        expect($this->mapper->resolve('INTEGER'))->toBe('number');
    });
});
