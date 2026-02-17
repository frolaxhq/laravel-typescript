<?php

namespace Frolax\Typescript;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Frolax\Typescript\Commands\TypescriptCommand;

class TypescriptServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-typescript')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_typescript_table')
            ->hasCommand(TypescriptCommand::class);
    }
}
