<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Commands;

use Doctrine\DBAL\Exception;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverFacade;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Trait_;
use GiacomoMasseroni\LaravelModelsGenerator\Exceptions\DatabaseDriverNotFound;
use GiacomoMasseroni\LaravelModelsGenerator\LaravelVersion;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\WriterInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;

class LaravelModelsGeneratorCommand extends Command
{
    public $signature = 'laravel-models-generator:generate
                        {--s|schema= : The name of the database}
                        {--c|connection= : The name of the connection}
                        {--t|table= : The name of the table}
                        {--f|factory : Generate a factory for each model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models from existing database';

    private ?string $singleEntityToCreate = null;

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @throws DatabaseDriverNotFound
     * @throws \Exception
     */
    public function handle(): int
    {
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $this->singleEntityToCreate = $this->getTable();
        $generateFactories = $this->option('factory');

        $connector = DriverFacade::instance(
            (string) config('database.connections.'.config('database.default').'.driver'),
            $connection,
            $schema,
            $this->singleEntityToCreate
        );

        $dbTables = $connector->listTables();

        $dbViews = config('models-generator.generate_views', false) ? $connector->listViews() : [];

        if (count($dbTables) + count($dbViews) == 0) {
            $this->warn('There are no tables and/or views in the connection you used. Please check the config file.');

            return self::FAILURE;
        }

        $fileSystem = new Filesystem;

        $path = $this->sanitizeBaseClassesPath(config('models-generator.path', app_path('Models')));

        if (config('models-generator.clean_models_directory_before_generation', true)) {
            // $fileSystem->cleanDirectory($path);
            deleteAllInFolder($path, 'Scopes');
        }

        $createChildrenClasses = config('models-generator.base_files.generate_children_classes', true);

        $factoriesPath = database_path('factories');
        if ($generateFactories) {
            if (file_exists($path)) {
                $generateFactories = $this->confirm('Factories folder exists, do you want to overwrite all existent factories?');
            } else {
                $this->createFactoriesFolder($factoriesPath);
            }
        }

        /**
         * @var string $name
         * @var Entity $dbEntity
         */
        foreach (array_merge($dbTables, $dbViews) as $name => $dbEntity) {
            if ($this->entityToGenerate($name)) {
                $createBaseClass = config('models-generator.base_files.enabled', false);

                $dbEntity->hasFactory = $generateFactories && ! in_array($dbEntity->name, config('models-generator.exclude_factories', []));

                if ($createBaseClass) {
                    $baseClassesPath = $path.DIRECTORY_SEPARATOR.'Base';
                    $this->createBaseClassesFolder($baseClassesPath);
                    $dbEntity->abstract = config('models-generator.base_files.abstract', false);
                    $dbEntity->namespace = config('models-generator.namespace', 'App\Models').'\\Base';
                    $fileName = $dbEntity->className.'.php';
                    $fileSystem->put($baseClassesPath.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));

                    // Create the factory
                    if ($dbEntity->hasFactory) {
                        $factoryFileName = $dbEntity->className.'Factory.php';
                        $fileSystem->put($factoriesPath.DIRECTORY_SEPARATOR.$factoryFileName, $this->factoryContent($dbEntity->className, $dbEntity));
                    }

                    $dbEntity->cleanForBase();
                }

                if ($createChildrenClasses) {
                    $fileName = $dbEntity->className.'.php';
                    $fileSystem->put($path.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));
                } elseif ($dbEntity->hasFactory) {
                    // TODO: check if needed
                    $factoryFileName = $dbEntity->className.'Factory.php';
                    $fileSystem->put($factoriesPath.DIRECTORY_SEPARATOR.$factoryFileName, $this->factoryContent($dbEntity->className, $dbEntity));
                }
            }
        }

        $this->info($this->singleEntityToCreate === null ? 'Check out your models' : "Check out your {$this->singleEntityToCreate} model");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath();
    }

    /**
     *  Resolve the fully qualified path to the stub.
     */
    private function resolveStubPath(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim('/src/Entities/stubs/model.stub', '/')))
            ? $customPath
            : __DIR__.'/../Entities/stubs/model.stub';
    }

    protected function getFactoryStub(): string
    {
        return $this->resolveFactoryStubPath();
    }

    /**
     *  Resolve the fully qualified path to the stub.
     */
    private function resolveFactoryStubPath(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim('/src/Entities/stubs/Laravel9/factory.stub', '/')))
            ? $customPath
            : __DIR__.'/../Entities/stubs/Laravel9/factory.stub';
    }

    protected function getStubEmpty(): string
    {
        return $this->resolveStubEmptyPath();
    }

    /**
     *  Resolve the fully qualified path to the stub.
     */
    private function resolveStubEmptyPath(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim('/src/Entities/stubs/model_empty.stub', '/')))
            ? $customPath
            : __DIR__.'/../Entities/stubs/model_empty.stub';
    }

    /**
     * @throws \Exception
     */
    private function modelContent(string $className, Entity $dbEntity): string
    {
        $content = file_get_contents($this->getStub());
        $contentEmpty = file_get_contents($this->getStubEmpty());
        if ($content !== false) {
            $arImports = [];
            /** @var array<Trait_> $traits */
            $traits = [];

            if ($dbEntity->importLaravelModel()) {
                $arImports[] = config('models-generator.parent', 'Illuminate\Database\Eloquent\Model');
            }

            if (count($dbEntity->belongsTo) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\BelongsTo::class;
            }

            if (count($dbEntity->hasMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\HasMany::class;
            }

            if (count($dbEntity->belongsToMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\BelongsToMany::class;
            }

            if (count($dbEntity->morphTo) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\MorphTo::class;
            }

            if (count($dbEntity->morphMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\MorphMany::class;
            }

            if (count($dbEntity->uuids) > 0) {
                $arImports[] = HasUuids::class;
            }

            if (count($dbEntity->ulids) > 0) {
                $arImports[] = HasUlids::class;
            }

            foreach ($dbEntity->traits as $trait) {
                $arImports[] = $trait->value;
            }

            foreach ($dbEntity->interfaces as $interface) {
                $arImports[] = $interface;
            }

            if (! is_null($dbEntity->observer)) {
                if ($this->resolveLaravelVersion()->check(10, 44)) {
                    $arImports[] = 'Illuminate\Database\Eloquent\Attributes\ObservedBy';
                    $arImports[] = $dbEntity->observer;
                }
            }

            if (! is_null($dbEntity->queryBuilder)) {
                if ($this->resolveLaravelVersion()->check(12, 19)) {
                    $arImports[] = 'Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder';
                    $arImports[] = $dbEntity->queryBuilder;
                }
            }

            if (count($dbEntity->globalScopes) > 0) {
                foreach ($dbEntity->globalScopes as $globalScope) {
                    $arImports[] = $globalScope;
                }

                if ($this->resolveLaravelVersion()->check(10)) {
                    $arImports[] = 'Illuminate\Database\Eloquent\Attributes\ScopedBy';
                }
            }

            if ($dbEntity->softDeletes) {
                $arImports[] = SoftDeletes::class;
            }

            if (count($dbEntity->traits) > 0 && $dbEntity->hasFactory) {
                $arImports[] = 'Illuminate\Database\Eloquent\Factories\HasFactory';
                $traits[] = new Trait_(
                    'Illuminate\Database\Eloquent\Factories\HasFactory\HasFactory',
                    $this->resolveLaravelVersion()->check(11) ? '/** @use HasFactory<\Database\Factories\\'.$dbEntity->className.'Factory> */' : null
                );
            }

            $dbEntity->imports = array_merge($dbEntity->imports, $arImports);
            $dbEntity->traits = array_merge($dbEntity->traits, $traits);

            if ($dbEntity instanceof Table) {
                $dbEntity->fixRelationshipsName();
            }

            $versionedWriter = 'GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Laravel'.$this->resolveLaravelVersion()->major.'\\Writer';
            /** @var WriterInterface $writer */
            $writer = new $versionedWriter($className, $dbEntity, $content, $contentEmpty);

            return $writer->writeModelFile();
        }

        throw new \Exception('Error reading stub file');
    }

    /**
     * @throws \Exception
     */
    private function factoryContent(string $className, Entity $dbEntity): string
    {
        $content = file_get_contents($this->getFactoryStub());
        if ($content !== false) {
            $versionedWriter = 'GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory\Laravel'.$this->resolveLaravelVersion()->major.'\\Writer';
            /** @var \GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory\WriterInterface $writer */
            $writer = new $versionedWriter($className, $dbEntity, $content);

            return $writer->writeFactoryFile();
        }

        throw new \Exception('Error reading stub file');
    }

    private function getConnection(): string
    {
        return $this->option('connection') ?: config('database.default');
    }

    private function getSchema(string $connection): string
    {
        return $this->option('schema') ?: config('database.connections.'.$connection.'.database');
    }

    private function getTable(): ?string
    {
        $table = $this->option('table');

        return is_string($table) ? $table : null;
    }

    private function entityToGenerate(string $entity): bool
    {
        return ! in_array($entity, config('models-generator.except', [])) && $this->singleEntityToCreate === null || ($this->singleEntityToCreate && $this->singleEntityToCreate === $entity);
    }

    private function sanitizeBaseClassesPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function createBaseClassesFolder(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        /*if (! file_exists(base_path($path))) {
            mkdir(base_path($path), 0755, true);
        }*/
    }

    private function resolveLaravelVersion(): LaravelVersion
    {
        static $version = null;

        if ($version === null) {
            $version = new LaravelVersion;
        }

        return $version;
    }

    private function createFactoriesFolder(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
