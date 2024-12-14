<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Commands;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverFacade;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel11\Writer;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

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

    /**
     * @var array<string, mixed>
     */
    private static array $tableIndexes = [];

    /**
     * @var array<string, mixed>
     */
    private static array $tableColumns = [];

    private AbstractSchemaManager $sm;

    private ?string $connection = null;

    private ?string $schema = null;

    private ?string $singleTableToCreate = null;

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $this->connection = $this->getConnection();
        $this->schema = $this->getSchema($this->connection);
        $this->singleTableToCreate = $this->getTable();

        $dbTables = [];

        /*$connectionParams = [
            'dbname' => $schema,
            'user' => config('database.connections.'.config('database.default').'.username'),
            'password' => config('database.connections.'.config('database.default').'.password'),
            'host' => config('database.connections.'.config('database.default').'.host'),
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
            'path' => config('database.connections.'.config('database.default').'.database')
        ];*/

        $connector = DriverFacade::instance(
            config('database.connections.'.config('database.default').'.driver'),
            $this->connection,
            $this->schema,
            $this->singleTableToCreate
        );

        $conn = DriverManager::getConnection($connector->connectionParams());
        $platform = $conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $this->sm = $conn->createSchemaManager();

        $tables = $this->sm->listTables();

        if (count($tables) == 0) {
            $this->warn('There are no tables in the connection you used. Please check the config file.');

            return self::FAILURE;
        }

        $morphables = [];

        foreach ($tables as $table) {
            $fks = $table->getForeignKeys();
            $columns = $this->getTableColumns($table->getName());
            $indexes = $this->getTableIndexes($table->getName());
            $properties = [];

            $dbTable = new Table($table->getName(), $this->dbTableNameToModelName($table->getName()));
            if (isset($indexes['primary'])) {
                $dbTable->primaryKey = $indexes['primary']->getColumns()[0];
            }
            $dbTable->fillable = array_diff(array_keys($columns), ['created_at', 'updated_at', 'deleted_at']);
            if (in_array('password', $dbTable->fillable)) {
                $dbTable->hidden = ['password'];
            }
            $dbTable->timestamps = array_key_exists('created_at', $columns) && array_key_exists('updated_at', $columns);

            /** @var Column $column */
            foreach ($columns as $column) {
                if (($laravelColumnType = $this->laravelColumnType($column->getType(), $dbTable)) !== null) {
                    $dbTable->casts[$column->getName()] = $laravelColumnType;

                    $properties[] = $laravelColumnType.($column->getNotnull() ? '' : '|null').' $'.$column->getName();
                }

                // Get morph
                if (str_ends_with($column->getName(), '_type') && in_array(str_replace('_type', '', $column->getName()).'_id', array_keys($columns))) {
                    $dbTable->morphTo[] = new MorphTo(str_replace('_type', '', $column->getName()));

                    $morphables[str_replace('_type', '', $column->getName())] = $dbTable->className;
                }
            }
            $dbTable->properties = $properties;

            foreach ($fks as $fk) {
                $dbTable->belongsTo[$fk->getName()] = new BelongsTo($fk);
            }

            $dbTables[$table->getName()] = $dbTable;
        }

        foreach ($dbTables as $dbTable) {
            foreach ($dbTable->belongsTo as $foreignName => $belongsTo) {
                $foreignTableName = $belongsTo->foreignKey->getForeignTableName();
                //info('TABLE: '.$dbTable->name);
                //info(print_r($belongsTo->foreignKey, true));
                $foreignKeyName = $belongsTo->foreignKey->getLocalColumns()[0];
                $localKeyName = $belongsTo->foreignKey->getForeignColumns()[0];
                if ($localKeyName == $dbTables[$foreignTableName]->primaryKey) {
                    $localKeyName = null;
                }
                $dbTables[$foreignTableName]->addHasMany(new HasMany($dbTable->className, $foreignKeyName, $localKeyName));

                if (count($dbTable->belongsTo) > 1) {
                    foreach ($dbTable->belongsTo as $subForeignName => $subBelongsTo) {
                        $subForeignTableName = $subBelongsTo->foreignKey->getForeignTableName();
                        if ($foreignTableName != $subForeignTableName) {
                            //info('TABLE: '.$dbTable->name);
                            //info($subForeignTableName);
                            //info(print_r($belongsTo->foreignKey, true));

                            //info("Creating belongs to many in {$dbTables[$foreignTableName]->name} ({$subForeignTableName})");

                            $tableIndexes = $this->getTableIndexes($dbTables[$foreignTableName]->name);
                            $relatedTableIndexes = $this->getTableIndexes($subForeignTableName);
                            $pivotIndexes = $this->getTableIndexes($dbTable->name);

                            $foreignPivotKey = $tableIndexes['primary']->getColumns()[0];
                            $relatedPivotKey = $relatedTableIndexes['primary']->getColumns()[0];
                            $pivotPrimaryKey = $pivotIndexes['primary']->getColumns()[0];

                            $pivotColumns = $this->getTableColumns($dbTable->name);
                            $pivotAttributes = array_diff(array_keys($pivotColumns), [$foreignPivotKey, $relatedPivotKey, $pivotPrimaryKey]);

                            $belongsToMany = new BelongsToMany(
                                $subForeignTableName,
                                $dbTable->name,
                                $foreignPivotKey,
                                $relatedPivotKey,
                                pivotAttributes: $pivotAttributes
                            );
                            $belongsToMany->timestamps = array_key_exists('created_at', $pivotColumns) && array_key_exists('updated_at', $pivotColumns);

                            $dbTables[$foreignTableName]->belongsToMany[] = $belongsToMany;

                            // TODO: do not why I added this code, it seems not working
                            /*foreach ($dbTables[$foreignTableName]->hasMany as $key => $hasMany) {
                                if ($hasMany->name == $dbTable->name) {
                                    unset($dbTables[$foreignTableName]->hasMany[$key]);
                                }
                            }*/
                        }
                    }
                }
            }

            // Morph many
            foreach (config('models-generator.morphs') as $table => $relationship) {
                if ($table == $dbTable->name) {
                    $dbTable->morphMany[] = new MorphMany(
                        Str::camel(Str::plural($morphables[$relationship])),
                        $morphables[$relationship],
                        $relationship,
                    );
                }
            }
        }

        $fileSystem = new Filesystem;

        foreach ($dbTables as $name => $dbTable) {
            if ($this->tableToGenerate($name)) {
                $fileName = $dbTable->className.'.php';
                $fileSystem->put(app_path('Models'.DIRECTORY_SEPARATOR.$fileName), $this->modelContent($dbTable->className, $dbTable));
            }
        }
        $this->info($this->singleTableToCreate === null ? 'Check out your models' : "Check out your {$this->singleTableToCreate} model");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath();
    }

    /**
     * /**
     *  Resolve the fully-qualified path to the stub.
     */
    private function resolveStubPath(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim('/src/Entities/stubs/model.stub', '/')))
            ? $customPath
            : __DIR__.'/../Entities/stubs/model.stub';
    }

    protected function replaceClassName(string &$stub, string $table): static
    {
        $stub = str_replace('{{ class }}', $table, $stub);

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function modelContent(string $className, Table $dbTable): string
    {
        $content = file_get_contents($this->getStub());
        if ($content !== false) {
            $arImports = [
                config('models-generator.parent', 'Illuminate\Database\Eloquent\Model'),
            ];

            if (count($dbTable->belongsTo) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\BelongsTo::class;
            }

            if (count($dbTable->hasMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\HasMany::class;
            }

            if (count($dbTable->belongsToMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\BelongsToMany::class;
            }

            if (count($dbTable->morphTo) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\MorphTo::class;
            }

            if (count($dbTable->morphMany) > 0) {
                $arImports[] = \Illuminate\Database\Eloquent\Relations\MorphMany::class;
            }

            if (count(config('models-generator.traits', [])) > 0) {
                foreach (config('models-generator.traits') as $trait) {
                    $arImports[] = $trait;
                }
            }

            if (count(config('models-generator.interfaces', [])) > 0) {
                foreach (config('models-generator.interfaces') as $interface) {
                    $arImports[] = $interface;
                }
            }

            $dbTable->imports = array_merge($dbTable->imports, $arImports);

            $writer = new Writer($className, $dbTable, $content);

            return $writer->writeModelFile();
        }

        throw new \Exception('Error reading stub file');
    }

    /**
     * @param string $tableName
     * @return array<string, mixed>
     * @throws Exception
     */
    private function getTableIndexes(string $tableName): array
    {
        if (! isset(self::$tableIndexes[$tableName])) {
            self::$tableIndexes[$tableName] = $this->sm->listTableIndexes($tableName);
        }

        return self::$tableIndexes[$tableName];
    }

    /**
     * @param string $tableName
     * @return array<string, mixed>
     * @throws Exception
     */
    private function getTableColumns(string $tableName): array
    {
        if (! isset(self::$tableColumns[$tableName])) {
            self::$tableColumns[$tableName] = $this->sm->listTableColumns($tableName);
        }

        return self::$tableColumns[$tableName];
    }

    private function laravelColumnType(Type $type, Table $dbTable): ?string
    {
        if ($type instanceof BigIntType) {
            return 'int';
        }
        if ($type instanceof DateType) {
            $dbTable->imports[] = 'Datetime';

            return 'Datetime';
        }
        if ($type instanceof DateTimeType) {
            $dbTable->imports[] = 'Datetime';

            return 'Datetime';
        }
        if ($type instanceof StringType) {
            return 'string';
        }

        return null;
    }

    private function dbTableNameToModelName(string $dbTableName): string
    {
        return ucfirst(Str::camel(Str::singular($dbTableName)));
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
        return $this->option('table');
    }

    private function tableToGenerate(string $table): bool
    {
        return ! in_array($table, config('models-generator.except', [])) && $this->singleTableToCreate === null || ($this->singleTableToCreate && $this->singleTableToCreate === $table);
    }
}
