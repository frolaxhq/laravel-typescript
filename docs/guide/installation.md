# Installation

You can install the package via composer:

```bash
composer require frolaxhq/laravel-typescript
```

## Publish Configuration

Next, publish the configuration file:

```bash
php artisan vendor:publish --provider="Frolax\Typescript\TypescriptServiceProvider" --tag="typescript-config"
```

This will create a `config/typescript.php` file in your project.

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or higher
- TypeScript 4.x or higher
