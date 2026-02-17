# Auto-generation

`laravel-typescript` can automatically run the generation command when certain events occur in your application.

## Migration Hook

You can configure the generator to run automatically after your migrations have finished. This ensures your TypeScript types are always in sync with your latest database schema.

Enable this in `config/typescript.php`:

```php
'pipeline' => [
    // ...
    'after_migrate' => true,
],
```

When this is `true`, the `typescript:generate` command will be triggered whenever you run `php artisan migrate`.

## Manual Hook

If you prefer to trigger generation from your own code or service providers, you can use the `Artisan` facade:

```php
use Illuminate\Support\Facades\Artisan;

Artisan::call('typescript:generate');
```

## Continuous Integration (CI)

It is highly recommended to run the generation command as part of your CI/CD pipeline and fail the build if the types are out of sync (using the `--strict` flag):

```bash
php artisan typescript:generate --strict
```

This ensures that no changes to your models go untracked in your frontend.
