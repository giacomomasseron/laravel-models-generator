<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Tests;

use GiacomoMasseroni\LaravelModelsGenerator\LaravelModelsGeneratorServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'GiacomoMasseroni\\LaravelModelsGenerator\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModelsGeneratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-models-generator_table.php.stub';
        $migration->up();
        */
    }
}
