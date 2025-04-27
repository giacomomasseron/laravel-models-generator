<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Commands;

use Doctrine\DBAL\Exception;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverFacade;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Exceptions\DatabaseDriverNotFound;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\WriterInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;

class LaravelModelsGeneratorCommand extends Command
{
    public $signature = 'laravel-models-generator:generate
                        {--s|schema= : The name of the database}
                        {--c|connection= : The name of the connection}
                        {--t|table= : The name of the table}';

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
            $fileSystem->cleanDirectory($path);
        }

        /**
         * @var string $name
         * @var Entity $dbEntity
         */
        foreach (array_merge($dbTables, $dbViews) as $name => $dbEntity) {
            if ($this->entityToGenerate($name)) {
                $createBaseClass = config('models-generator.base_files.enabled', false);
                if ($createBaseClass) {
                    $baseClassesPath = $path.DIRECTORY_SEPARATOR.'Base';
                    $this->createBaseClassesFolder($baseClassesPath);
                    $dbEntity->abstract = config('models-generator.base_files.abstract', false);
                    $dbEntity->namespace = config('models-generator.namespace', 'App\Models').'\\Base';
                    $fileName = $dbEntity->className.'.php';
                    $fileSystem->put($baseClassesPath.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));

                    $dbEntity->cleanForBase();
                }

                $fileName = $dbEntity->className.'.php';
                $fileSystem->put($path.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));
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

            foreach ($dbEntity->traits as $trait) {
                $arImports[] = $trait;
            }

            foreach ($dbEntity->interfaces as $interface) {
                $arImports[] = $interface;
            }

            if ($dbEntity->softDeletes) {
                $arImports[] = SoftDeletes::class;
            }

            $dbEntity->imports = array_merge($dbEntity->imports, $arImports);

            if ($dbEntity instanceof Table) {
                $dbEntity->fixRelationshipsName();
            }

            $versionedWriter = 'GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel'.$this->resolveLaravelVersion().'\\Writer';
            /** @var WriterInterface $writer */
            $writer = new $versionedWriter($className, $dbEntity, $content, $contentEmpty);

            return $writer->writeModelFile();
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

    private function resolveLaravelVersion(): int
    {
        return (int) strstr(app()->version(), '.', true);
    }
}
