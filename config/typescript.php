<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Model Discovery
    |--------------------------------------------------------------------------
    */
    'discovery' => [
        // Directories to scan for Eloquent models
        'paths' => [
            app_path('Models'),
        ],

        // Automatically discover models in the codebase
        'auto_discover' => true,

        // Additional directories to scan (merged with paths)
        'additional_paths' => [],

        // Only generate for these models (empty = all). Use short or FQCN.
        'included_models' => [],

        // Skip these models. Use short or FQCN.
        'excluded_models' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Configuration
    |--------------------------------------------------------------------------
    */
    'output' => [
        // Where to write generated TypeScript files
        'path' => resource_path('js/types'),

        // true = one file per model, false = single bundled file
        'per_model_files' => false,

        // Filename when per_model_files is false
        'single_file_name' => 'models.d.ts',

        // Subdirectory for enum files (when per_model_files is true)
        'enum_directory' => 'enums',

        // Generate barrel export (index.ts) when per_model_files is true
        'barrel_export' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Writer Configuration
    |--------------------------------------------------------------------------
    */
    'writer' => [
        // Default writer: 'interface', 'type', or 'json'
        'default' => 'interface',

        // Enum output style: 'const_object', 'ts_enum', or 'union'
        'enum_style' => 'const_object',

        // Wrap all output in declare namespace X {}
        'global_namespace' => null,

        // Pluralize interface names (User → Users)
        'plurals' => false,

        // Generate API resource types (wraps in { data: T })
        'api_resources' => false,

        // Generate fillable-only types alongside full interfaces
        'fillable_types' => false,

        // Suffix for fillable types (e.g. UserFillable)
        'fillable_suffix' => 'Fillable',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relation Configuration
    |--------------------------------------------------------------------------
    */
    'relations' => [
        'enabled' => true,
        'optional' => false,
        'max_depth' => 1,

        'counts' => [
            'enabled' => true,
            'optional' => false,
        ],

        'exists' => [
            'enabled' => true,
            'optional' => false,
        ],

        'sums' => [
            'enabled' => true,
            'optional' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Type Mappings
    |--------------------------------------------------------------------------
    */
    'mappings' => [
        // Custom type overrides (PHP/DB type → TypeScript type)
        // These take precedence over built-in mappings.
        'custom' => [
            // 'point' => '{ lat: number; lng: number }',
            // 'money' => 'string',
        ],

        // Map timestamp columns to Date instead of string
        'timestamps_as_date' => false,

        // Map decimal/numeric to string instead of number
        'decimals_as_string' => true,

        // Standalone custom types (name => TypeScript definition)
        // These will be generated as 'export interface Name { ... }' or 'export type Name = ...'
        'standalone' => [
            // 'Image' => '{ original: string; thumbnail: string }',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming Convention (Case)
    |--------------------------------------------------------------------------
    */
    'case' => [
        // Column name case: 'snake', 'camel', 'pascal'
        'columns' => 'snake',

        // Relation name case: 'snake', 'camel', 'pascal'
        'relations' => 'snake',
    ],

    /*
    |--------------------------------------------------------------------------
    | Visibility
    |--------------------------------------------------------------------------
    */
    'visibility' => [
        // Include $hidden columns in output (as optional properties)
        'include_hidden' => true,

        // Make nullable columns optional (name?: type) instead of (name: type | null)
        'optional_nullables' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Introspection
    |--------------------------------------------------------------------------
    */
    'introspection' => [
        // Force a specific database connection for schema reading
        // null = use each model's configured connection
        'connection' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pipeline Behavior
    |--------------------------------------------------------------------------
    */
    'pipeline' => [
        // If true, stop generation on first model processing error
        // If false, skip erroring models and continue
        'bail_on_error' => false,

        // Auto-run typescript:generate after migrations
        'after_migrate' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching / Incremental Builds
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // Enable incremental builds (skip unchanged models)
        'enabled' => false,

        // Cache store to use (null = default)
        'store' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Formatter Integration
    |--------------------------------------------------------------------------
    */
    'formatter' => [
        // Auto-format output using an external formatter
        'enabled' => false,

        // Formatter to use: 'prettier', 'biome', or null
        'tool' => null,

        // Path to the formatter binary (null = auto-detect)
        'binary' => null,

        // Formatter config file path (null = use project default)
        'config' => null,
    ],

];
