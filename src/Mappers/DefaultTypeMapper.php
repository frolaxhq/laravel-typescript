<?php

declare(strict_types=1);

namespace Frolax\Typescript\Mappers;

use Frolax\Typescript\Contracts\TypeMapperContract;

/**
 * Default type mapper providing canonical DB type → TypeScript mappings.
 */
class DefaultTypeMapper implements TypeMapperContract
{
    /**
     * @var array<string, string>
     */
    private const MAPPINGS = [
        // Strings
        'string' => 'string',
        'text' => 'string',
        'char' => 'string',
        'varchar' => 'string',
        'guid' => 'string',

        // Numbers
        'integer' => 'number',
        'int' => 'number',
        'smallint' => 'number',
        'mediumint' => 'number',
        'bigint' => 'number',
        'float' => 'number',
        'double' => 'number',
        'decimal' => 'number',
        'year' => 'number',

        // Boolean
        'boolean' => 'boolean',
        'bool' => 'boolean',

        // Date/Time
        'date' => 'string',
        'datetime' => 'string',
        'timestamp' => 'string',
        'time' => 'string',

        // Special
        'uuid' => 'string',
        'ulid' => 'string',
        'json' => 'Record<string, unknown>',
        'binary' => 'Blob',
        'enum' => 'string',
    ];

    public function supports(string $type): bool
    {
        return isset(self::MAPPINGS[strtolower($type)]);
    }

    public function resolve(string $type, array $parameters = []): string
    {
        return self::MAPPINGS[strtolower($type)] ?? 'unknown';
    }
}
