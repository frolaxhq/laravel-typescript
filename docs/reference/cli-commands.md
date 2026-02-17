# CLI Commands

The primary way to interact with `laravel-typescript` is through its Artisan commands.

## typescript:generate

The main command used to generate TypeScript definitions.

```bash
php artisan typescript:generate
```

### Options

| Option | Description |
| --- | --- |
| `--stdout` | Output the generated content to stdout instead of writing to files. |
| `--strict` | Fail the command with a non-zero exit code if any errors occur during generation. |
| `--no-format` | Disable automatic formatting even if enabled in the configuration. |
| `--connection=X` | Force a specific database connection for schema introspection. |
| `--models=X,Y` | Only generate for the specified model names (comma-separated). |

## typescript:publish

Publishes the package configuration file.

```bash
php artisan typescript:publish
```
