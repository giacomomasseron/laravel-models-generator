<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Commands;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallFloatType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverFacade;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\PrimaryKey;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
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
     * @var array<string, string>
     */
    private array $typeColumnPropertyMaps = [
        'datetime' => 'Carbon',
    ];

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
                //$dbTable->primaryKey = $indexes['primary']->getColumns()[0];
                $primaryKeyName = $indexes['primary']->getColumns()[0];
                foreach ($columns as $column) {
                    if ($column->getName() == $indexes['primary']->getColumns()[0]) {
                        $dbTable->primaryKey = new PrimaryKey($primaryKeyName, $column->getAutoincrement(), $this->laravelColumnType($column->getType()));
                    }
                    break;
                }
            }
            $dbTable->fillable = array_diff(
                array_keys($columns),
                array_merge(
                    ['created_at', 'updated_at', 'deleted_at'],
                    (config('models-generator.primary_key_in_fillable', false) && ! empty($dbTable->primaryKey->name) ? [] : [$dbTable->primaryKey->name])
                )
            );
            if (in_array('password', $dbTable->fillable)) {
                $dbTable->hidden = ['password'];
            }
            $dbTable->timestamps = array_key_exists('created_at', $columns) && array_key_exists('updated_at', $columns);
            $dbTable->softDeletes = array_key_exists('deleted_at', $columns);

            /** @var Column $column */
            foreach ($columns as $column) {
                if (($laravelColumnType = $this->laravelColumnType($column->getType(), $dbTable)) !== null) {
                    $dbTable->casts[$column->getName()] = $laravelColumnType;

                    $properties[] = new Property('$'.$column->getName(), ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType).($column->getNotnull() ? '' : '|null')); //$laravelColumnType.($column->getNotnull() ? '' : '|null').' $'.$column->getName();
                }

                // Get morph
                if (str_ends_with($column->getName(), '_type') && in_array(str_replace('_type', '', $column->getName()).'_id', array_keys($columns))) {
                    $dbTable->morphTo[] = new MorphTo(str_replace('_type', '', $column->getName()));

                    $morphables[str_replace('_type', '', $column->getName())] = $dbTable->className;
                }
            }
            $dbTable->properties = $properties;

            foreach ($fks as $fk) {
                $dbTable->addBelongsTo(new BelongsTo($fk));
                //$dbTable->belongsTo[$fk->getName()] = new BelongsTo($fk);
            }

            $dbTables[$table->getName()] = $dbTable;
        }

        foreach ($dbTables as $dbTable) {
            foreach ($dbTable->belongsTo as $foreignName => $belongsTo) {
                $foreignTableName = $belongsTo->foreignKey->getForeignTableName();
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

                            $tableIndexes = $this->getTableIndexes($dbTables[$foreignTableName]->name);
                            $relatedTableIndexes = $this->getTableIndexes($subForeignTableName);
                            $pivotIndexes = $this->getTableIndexes($dbTable->name);

                            $foreignPivotKey = $tableIndexes['primary']->getColumns()[0];
                            $relatedPivotKey = $relatedTableIndexes['primary']->getColumns()[0];
                            $pivotPrimaryKey = $pivotIndexes['primary']->getColumns()[0];

                            $pivotColumns = $this->getTableColumns($dbTable->name);
                            $pivotTimestamps = array_key_exists('created_at', $pivotColumns) && array_key_exists('updated_at', $pivotColumns);
                            $pivotAttributes = array_diff(
                                array_keys($pivotColumns),
                                array_merge(
                                    [$foreignPivotKey, $relatedPivotKey, $pivotPrimaryKey],
                                    $pivotTimestamps ? ['created_at', 'updated_at'] : []
                                )
                            );

                            $belongsToMany = new BelongsToMany(
                                $subForeignTableName,
                                $dbTable->name,
                                $foreignPivotKey,
                                $relatedPivotKey,
                                pivotAttributes: $pivotAttributes
                            );
                            $belongsToMany->timestamps = $pivotTimestamps;

                            $dbTables[$foreignTableName]->addBelongsToMany($belongsToMany);

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

        if (config('models-generator.clean_models_directory_before_generation', true)) {
            $fileSystem->cleanDirectory(app_path('Models'));
        }

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
            if ($dbTable->softDeletes) {
                $arImports[] = \Illuminate\Database\Eloquent\SoftDeletes::class;
            }

            $dbTable->imports = array_merge($dbTable->imports, $arImports);

            $dbTable->fixRelationshipsName();

            $writer = new Writer($className, $dbTable, $content);

            return $writer->writeModelFile();
        }

        throw new \Exception('Error reading stub file');
    }

    /**
     * @return array<string, mixed>
     *
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
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function getTableColumns(string $tableName): array
    {
        if (! isset(self::$tableColumns[$tableName])) {
            self::$tableColumns[$tableName] = $this->sm->listTableColumns($tableName);
        }

        return self::$tableColumns[$tableName];
    }

    private function laravelColumnType(Type $type, ?Table $dbTable = null): ?string
    {
        if ($type instanceof SmallIntType ||
            $type instanceof BigIntType ||
            $type instanceof IntegerType
        ) {
            return 'int';
        }
        if ($type instanceof DateType ||
            $type instanceof DateTimeType ||
            $type instanceof DateImmutableType ||
            $type instanceof DateTimeImmutableType ||
            $type instanceof DateTimeTzType ||
            $type instanceof DateTimeTzImmutableType
        ) {
            if ($dbTable !== null) {
                $dbTable->imports[] = 'Carbon\Carbon';
            }

            return 'datetime';
        }
        if ($type instanceof StringType ||
            $type instanceof TextType) {
            return 'string';
        }
        if ($type instanceof DecimalType ||
            $type instanceof SmallFloatType ||
            $type instanceof FloatType
        ) {
            return 'float';
        }
        if ($type instanceof BooleanType) {
            return 'bool';
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
