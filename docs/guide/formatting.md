# Formatting

`laravel-typescript` can automatically format its output to match your project's coding style.

## Formatter Integration

The generator can integrate with popular tools like **Prettier** or **Biome**. When enabled, the generated files will be automatically piped through your chosen formatter.

Configure this in `config/typescript.php`:

```php
'formatter' => [
    'enabled' => true,
    'tool' => 'prettier', // or 'biome', or null
    'binary' => null,   // null = auto-detect in node_modules or PATH
    'config' => null,   // null = use project default config file
],
```

## How It Works

1. **Generation**: The generator produces the TypeScript content.
2. **Formatting**: If enabled, the content is sent to the formatter's CLI.
3. **Writing**: The formatted content is written to the final output file.

## Manual Formatting

If you prefer to format the files yourself (e.g., using a VS Code extension or a git hook), you can disable built-in formatting by setting `enabled` to `false`.

You can also bypass formatting for a single run using the `--no-format` flag:

```bash
php artisan typescript:generate --no-format
```
