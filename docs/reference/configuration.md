# Configuration

The documentation for all configuration options available in `config/typescript.php`.

## Discovery

```php
'discovery' => [
    // Directories to scan for Eloquent models
    'paths' => [
        app_path('Models'),
    ],

    // Additional directories to scan (merged with paths)
    'additional_paths' => [],

    // Only generate for these models (empty = all)
    'included_models' => [],

    // Skip these models
    'excluded_models' => [],
],
```

## Output

```php
'output' => [
    // Where to write generated TypeScript files
    'path' => resource_path('types/generated'),

    // true = one file per model, false = single bundled file
    'per_model_files' => false,

    // Filename when per_model_files is false
    'single_file_name' => 'models.d.ts',

    // Subdirectory for enum files (when per_model_files is true)
    'enum_directory' => 'enums',

    // Generate barrel export (index.ts) when per_model_files is true
    'barrel_export' => true,
],
```

## Writer

```php
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
```

## Relations

```php
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
```

## Pipeline

```php
'pipeline' => [
    // Stop on first error
    'bail_on_error' => false,

    // Auto-run generate after migrations
    'after_migrate' => false,
],
```
