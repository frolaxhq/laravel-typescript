<?php

declare(strict_types=1);

namespace Frolax\Typescript\Introspection;

use Frolax\Typescript\Contracts\SchemaIntrospectorContract;
use Frolax\Typescript\Data\RawColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Default schema introspector using Laravel's schema builder.
 * Works with MySQL, PostgreSQL, SQLite, and SQL Server.
 */
class LaravelSchemaIntrospector implements SchemaIntrospectorContract
{
    /**
     * Type normalization map (raw DB types → canonical types).
     *
     * @var array<string, string>
     */
    private const TYPE_NORMALIZATIONS = [
        'character varying' => 'string',
        'varchar' => 'string',
        'char' => 'string',
        'character' => 'string',
        'nvarchar' => 'string',
        'nchar' => 'string',
        'text' => 'text',
        'tinytext' => 'text',
        'mediumtext' => 'text',
        'longtext' => 'text',
        'ntext' => 'text',
        'citext' => 'text',
        'int' => 'integer',
        'integer' => 'integer',
        'int2' => 'integer',
        'int4' => 'integer',
        'int8' => 'bigint',
        'smallint' => 'integer',
        'mediumint' => 'integer',
        'bigint' => 'bigint',
        'tinyint' => 'boolean',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'float' => 'float',
        'float4' => 'float',
        'float8' => 'double',
        'double' => 'double',
        'double precision' => 'double',
        'real' => 'float',
        'decimal' => 'decimal',
        'numeric' => 'decimal',
        'money' => 'decimal',
        'date' => 'date',
        'datetime' => 'datetime',
        'datetime2' => 'datetime',
        'datetimeoffset' => 'datetime',
        'timestamp' => 'timestamp',
        'timestamp with time zone' => 'timestamp',
        'timestamp without time zone' => 'timestamp',
        'timestamptz' => 'timestamp',
        'time' => 'time',
        'time with time zone' => 'time',
        'time without time zone' => 'time',
        'timetz' => 'time',
        'year' => 'year',
        'json' => 'json',
        'jsonb' => 'json',
        'uuid' => 'uuid',
        'uniqueidentifier' => 'uuid',
        'ulid' => 'ulid',
        'binary' => 'binary',
        'varbinary' => 'binary',
        'blob' => 'binary',
        'tinyblob' => 'binary',
        'mediumblob' => 'binary',
        'longblob' => 'binary',
        'bytea' => 'binary',
        'image' => 'binary',
        'enum' => 'enum',
        'set' => 'string',
        'point' => 'json',
        'polygon' => 'json',
        'geometry' => 'json',
        'geography' => 'json',
        'linestring' => 'json',
        'multipoint' => 'json',
        'multilinestring' => 'json',
        'multipolygon' => 'json',
        'geometrycollection' => 'json',
        'inet' => 'string',
        'cidr' => 'string',
        'macaddr' => 'string',
        'macaddr8' => 'string',
        'bit' => 'boolean',
        'varbit' => 'string',
        'xml' => 'string',
    ];

    public function supports(string $driver): bool
    {
        return in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv']);
    }

    /**
     * @return Collection<int, RawColumn>
     */
    public function getColumns(Model $model): Collection
    {
        $connection = $model->getConnection();
        $table = $model->getTable();
        $columns = $connection->getSchemaBuilder()->getColumns($table);

        return collect($columns)->map(function (array $column) {
            $rawType = $column['type'];
            $normalizedType = $this->normalizeType($rawType);

            return new RawColumn(
                name: $column['name'],
                type: $normalizedType,
                rawType: $rawType,
                nullable: $column['nullable'],
                default: $column['default'],
                autoIncrement: $column['auto_increment'],
                unique: false, // Schema builder doesn't easily expose this
            );
        });
    }

    public function getColumnType(Model $model, string $column): string
    {
        $columns = $this->getColumns($model);
        $col = $columns->first(fn (RawColumn $c) => $c->name === $column);

        return $col->type ?? 'string';
    }

    /**
     * Normalize a raw database type string to a canonical type.
     */
    private function normalizeType(string $rawType): string
    {
        // Strip size/precision suffixes: varchar(255) → varchar, decimal(10,2) → decimal
        $baseType = Str::before(strtolower(trim($rawType)), '(');
        $baseType = trim($baseType);

        // Strip unsigned
        $baseType = str_replace(' unsigned', '', $baseType);
        $baseType = trim($baseType);

        // Check for char(36) which is commonly UUID
        if (($baseType === 'char' || $baseType === 'character') && Str::contains($rawType, '36')) {
            return 'uuid';
        }

        // Check for tinyint(1) which is boolean
        if ($baseType === 'tinyint' && Str::contains($rawType, '(1)')) {
            return 'boolean';
        }

        // Normalize tinyint with other sizes to integer
        if ($baseType === 'tinyint') {
            return 'integer';
        }

        return self::TYPE_NORMALIZATIONS[$baseType] ?? $baseType;
    }
}
