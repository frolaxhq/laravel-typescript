<?php

declare(strict_types=1);

use Frolax\Typescript\Support\CaseFormatter;

describe('CaseFormatter', function () {
    beforeEach(function () {
        $this->formatter = new CaseFormatter;
    });

    it('formats to snake_case', function () {
        expect($this->formatter->format('firstName', 'snake'))->toBe('first_name');
        expect($this->formatter->format('FirstName', 'snake'))->toBe('first_name');
        expect($this->formatter->format('first_name', 'snake'))->toBe('first_name');
    });

    it('formats to camelCase', function () {
        expect($this->formatter->format('first_name', 'camel'))->toBe('firstName');
        expect($this->formatter->format('FirstName', 'camel'))->toBe('firstName');
    });

    it('formats to PascalCase', function () {
        expect($this->formatter->format('first_name', 'pascal'))->toBe('FirstName');
        expect($this->formatter->format('firstName', 'pascal'))->toBe('FirstName');
    });

    it('formats to kebab-case', function () {
        expect($this->formatter->format('firstName', 'kebab'))->toBe('first-name');
        expect($this->formatter->format('FirstName', 'kebab'))->toBe('first-name');
    });

    it('returns original for unknown case', function () {
        expect($this->formatter->format('first_name', 'unknown'))->toBe('first_name');
    });

    it('formats type name with pluralization', function () {
        expect($this->formatter->formatTypeName('User', false))->toBe('User');
        expect($this->formatter->formatTypeName('User', true))->toBe('Users');
        expect($this->formatter->formatTypeName('Post', true))->toBe('Posts');
    });
});
