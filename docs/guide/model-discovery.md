# Model Discovery

The generator works by scanning your application directories and using reflection to introspect your Eloquent models.

## Search Paths

You can specify which directories to scan in your `config/typescript.php`:

```php
'discovery' => [
    'paths' => [
        app_path('Models'),
    ],
    'additional_paths' => [
        base_path('packages/my-package/src/Models'),
    ],
],
```

## Including & Excluding Models

If you only want to generate types for a subset of your models, or want to skip certain ones, use the `included_models` and `excluded_models` arrays:

```php
'discovery' => [
    'included_models' => [
        \App\Models\User::class,
        'Post', // Short name also works
    ],
    'excluded_models' => [
        \App\Models\Secret::class,
    ],
],
```

## How It Works

1. **Scanning**: The generator finds all PHP files in the configured paths.
2. **Filtering**: It filters out files that don't extend `Illuminate\Database\Eloquent\Model`.
3. **Introspection**: For each model, it uses:
    - **Reflection**: To find methods, relations, and docblocks.
    - **Database Schema**: To discover column names and types.
    - **Model Information**: To find casts, hidden fields, and fillable attributes.
