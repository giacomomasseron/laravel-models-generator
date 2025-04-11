<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator;

use GiacomoMasseroni\LaravelModelsGenerator\Commands\LaravelModelsGeneratorAliasCommand;
use GiacomoMasseroni\LaravelModelsGenerator\Commands\LaravelModelsGeneratorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelModelsGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */

        // Check if the alias command is enabled in the config
        if (config('models-generator.enable_alias')) {
            $package
                ->name('laravel-models-generator')
                ->hasConfigFile()
                ->hasCommand(LaravelModelsGeneratorCommand::class)
                ->hasCommand(LaravelModelsGeneratorAliasCommand::class);
        } else {
            $package
                ->name('laravel-models-generator')
                ->hasConfigFile()
                ->hasCommand(LaravelModelsGeneratorCommand::class);
        }
    }
}
