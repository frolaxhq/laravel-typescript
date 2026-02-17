# Caching & Incremental Builds

For large applications with hundreds of models, generating TypeScript definitions from scratch every time can slow down your development flow. `laravel-typescript` includes a powerful incremental build system to keep things fast.

## Incremental Builds

When enabled, the generator tracks the modification time and content hash of your model files and their underlying database schema. It only re-generates TypeScript for models that have actually changed.

Enable this in your `config/typescript.php`:

```php
'cache' => [
    'enabled' => true,
    'store' => 'file', // null = use project default
],
```

## How It Works

1. **Hash Generation**: Before processing a model, the generator creates a hash based on the file content and the table schema.
2. **Cache Check**: It checks if a generated version for this hash already exists.
3. **Execution**: If a match is found, the generation for that specific model is skipped.
4. **Cleanup**: Stale cache entries are automatically cleaned up.

## Forcing Generation

If you ever need to bypass the cache and force a fresh generation of all types, you can clear the Laravel cache:

```bash
php artisan cache:clear
```
