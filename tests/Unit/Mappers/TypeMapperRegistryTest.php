<?php

declare(strict_types=1);

use Frolax\Typescript\Contracts\TypeMapperContract;
use Frolax\Typescript\Mappers\DefaultTypeMapper;
use Frolax\Typescript\Mappers\TypeMapperRegistry;

describe('TypeMapperRegistry', function () {
    beforeEach(function () {
        $this->registry = new TypeMapperRegistry();
    });

    it('supports default types', function () {
        expect($this->registry->supports('string'))->toBeTrue();
        expect($this->registry->supports('integer'))->toBeTrue();
        expect($this->registry->supports('boolean'))->toBeTrue();
    });

    it('resolves default types', function () {
        expect($this->registry->resolve('string'))->toBe('string');
        expect($this->registry->resolve('integer'))->toBe('number');
    });

    it('returns unknown for unsupported types', function () {
        expect($this->registry->resolve('foobar'))->toBe('unknown');
    });

    it('gives priority to later registered mappers', function () {
        // Create a custom mapper that overrides 'string' to 'text'
        $customMapper = new class implements TypeMapperContract {
            public function supports(string $type): bool
            {
                return $type === 'string';
            }
            public function resolve(string $type, array $parameters = []): string
            {
                return 'custom_string';
            }
        };

        $this->registry->register($customMapper);

        expect($this->registry->resolve('string'))->toBe('custom_string');
        // Other types still work via default mapper
        expect($this->registry->resolve('integer'))->toBe('number');
    });

    it('supports custom types via registered mappers', function () {
        $customMapper = new class implements TypeMapperContract {
            public function supports(string $type): bool
            {
                return $type === 'money';
            }
            public function resolve(string $type, array $parameters = []): string
            {
                return 'string';
            }
        };

        $this->registry->register($customMapper);

        expect($this->registry->supports('money'))->toBeTrue();
        expect($this->registry->resolve('money'))->toBe('string');
    });
});
