<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Commands;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class LaravelModelsGeneratorCommand extends Command
{
    public $signature = 'laravel-models-generator:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models from existing database';

    private static array $tableIndexes = [];

    private static array $tableColumns = [];

    private AbstractSchemaManager $sm;

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $dbTables = [];

        $connectionParams = [
            'dbname' => config('database.connections.mysql.database'),
            'user' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'host' => config('database.connections.mysql.host'),
            'driver' => 'pdo_mysql',
        ];

        $conn = DriverManager::getConnection($connectionParams);
        $platform = $conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $this->sm = $conn->createSchemaManager();

        $tables = $this->sm->listTables();

        foreach ($tables as $table) {
            $fks = $table->getForeignKeys();
            $columns = $this->getTableColumns($table->getName());
            $indexes = $this->getTableIndexes($table->getName());

            $dbTable = new Table($table->getName(), ucfirst(Str::camel(Str::singular($table->getName()))));
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
                if (($laravelColumnType = $this->laravelColumnType($column->getType())) !== null) {
                    $dbTable->casts[$column->getName()] = $laravelColumnType;
                }
            }

            foreach ($fks as $fk) {
                $dbTable->belongsTo[$fk->getForeignTableName()] = new BelongsTo($fk);
            }

            $dbTables[$table->getName()] = $dbTable;
        }

        foreach ($dbTables as $dbTable) {
            foreach ($dbTable->belongsTo as $foreignTableName => $belongsTo) {
                //info('TABLE: '.$dbTable->name);
                //info(print_r($belongsTo->foreignKey, true));
                $foreignKeyName = $belongsTo->foreignKey->getLocalColumns()[0];
                $localKeyName = $belongsTo->foreignKey->getForeignColumns()[0];
                if ($localKeyName == $dbTables[$foreignTableName]->primaryKey) {
                    $localKeyName = null;
                }
                $dbTables[$foreignTableName]->hasMany[] = new HasMany($dbTable->className, $foreignKeyName, $localKeyName);

                if (count($dbTable->belongsTo) > 1) {
                    foreach ($dbTable->belongsTo as $subForeignTableName => $subBelongsTo) {
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
        }

        $fileSystem = new Filesystem;

        foreach ($dbTables as $dbTable) {
            $fileName = $dbTable->className.'.php';
            $fileSystem->put(app_path('Models'.DIRECTORY_SEPARATOR.$fileName), $this->modelContent($dbTable->className, $dbTable));
        }
        $this->info('Check out your models');

        //info(print_r($dbTables, true));

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

    protected function replaceClassName(&$stub, string $table): static
    {
        $stub = str_replace('{{ class }}', $table, $stub);

        return $this;
    }

    private function modelContent(string $className, Table $dbTable): string
    {
        $content = file_get_contents($this->getStub());
        $namespace = 'App\Models';
        $arImports = [
            'use Illuminate\Database\Eloquent\Model;',
        ];
        $parent = 'Model';
        $body = '';

        if (count($dbTable->belongsTo) > 0) {
            $arImports[] = 'use Illuminate\Database\Eloquent\Relations\BelongsTo;';
        }

        if (count($dbTable->hasMany) > 0) {
            $arImports[] = 'use Illuminate\Database\Eloquent\Relations\HasMany;';
        }

        if (count($dbTable->belongsToMany) > 0) {
            $arImports[] = 'use Illuminate\Database\Eloquent\Relations\BelongsToMany;';
        }

        $body .= '    public $table = \''.$dbTable->name.'\';'."\n"."\n";

        $body .= '    public $primaryKey = \''.$dbTable->primaryKey.'\';'."\n"."\n";

        $body .= '    public $timestamps = '.($dbTable->timestamps ? 'true' : 'false').';'."\n"."\n";

        if (count($dbTable->hidden) > 0) {
            $body .= '    protected $hidden = ['."\n";
            foreach ($dbTable->hidden as $hidden) {
                $body .= '        \''.$hidden.'\','."\n";
            }
            $body .= '    ];'."\n"."\n";
        }

        if (count($dbTable->fillable) > 0) {
            $body .= '    protected $fillable = ['."\n";
            foreach ($dbTable->fillable as $fillable) {
                $body .= '        \''.$fillable.'\','."\n";
            }
            $body .= '    ];'."\n"."\n";
        }

        if (count($dbTable->casts) > 0) {
            $body .= '    /**'."\n";
            $body .= '     * @return array<string, string>'."\n";
            $body .= '     */'."\n";
            $body .= '    protected function casts(): array'."\n";
            $body .= '    {'."\n";
            $body .= '        return ['."\n";
            foreach ($dbTable->casts as $column => $type) {
                $body .= '            \''.$column.'\' => '.'\''.$type.'\','."\n";
            }
            $body .= '        ];'."\n";
            $body .= '    }'."\n";
        }

        foreach ($dbTable->hasMany as $hasMany) {
            $body .= '
    public function '.Str::camel(Str::plural($hasMany->name)).'(): HasMany
	{
		return $this->hasMany('.ucfirst(Str::camel($hasMany->name)).'::class, \''.$hasMany->foreignKeyName.'\''.(! empty($hasMany->localKeyName) ? ', \''.$hasMany->localKeyName.'\'' : '').');
	}'."\n";
        }

        foreach ($dbTable->belongsTo as $belongsTo) {
            $relationName = Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName()));
            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName())));
            $foreignColumnName = $belongsTo->foreignKey->getForeignColumns()[0];
            $body .= '
    public function '.$relationName.'(): BelongsTo
	{
		return $this->belongsTo('.$foreignClassName.'::class, \''.$foreignColumnName.'\');
	}'."\n";
        }

        foreach ($dbTable->belongsToMany as $belongsToMany) {
            if ($belongsToMany->pivot == $dbTable->name.'_'.$belongsToMany->related ||
                $belongsToMany->pivot == $belongsToMany->related.'_'.$dbTable->name) {
                $relationName = Str::camel(Str::plural($belongsToMany->related));
            } else {
                if (Str::start($belongsToMany->related, $belongsToMany->pivot)) {
                    $related = str_replace($belongsToMany->pivot.'_', '', $belongsToMany->related);
                } else {
                    $related = $belongsToMany->related;
                }
                $relationName = Str::camel(str_replace("{$dbTable->name}_", '', $belongsToMany->pivot).'_'.Str::plural($related));
            }

            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsToMany->related)));
            //$foreignColumnName = $belongsTo->foreignKey->getForeignColumns()[0];
            $body .= '
    public function '.$relationName.'(): BelongsToMany
	{
		return $this->belongsToMany('.$foreignClassName.'::class, \''.$belongsToMany->pivot.'\', \''.$belongsToMany->foreignPivotKey.'\', \''.$belongsToMany->relatedPivotKey.'\')
            '.(count($belongsToMany->pivotAttributes) > 0 ? '->withPivot(\''.implode('\', \'', $belongsToMany->pivotAttributes).'\')' : '').'
            '.($belongsToMany->timestamps ? '->withTimestamps()' : '').';
	}'."\n";
        }

        $search = [
            '{{namespace}}',
            '{{class}}',
            '{{imports}}',
            '{{parent}}',
            '{{body}}',
        ];
        $replace = [
            $namespace,
            $className,
            implode("\n", $arImports),
            $parent,
            $body,
        ];

        $content = str_replace($search, $replace, $content);

        if (file_exists(app_path('Models/CruratedCore/Concerns/'.$className.'Trait.php'))) {
            $content = str_replace('{{traits}}', 'use \App\Models\CruratedCore\Concerns\\'.$className.'Trait;', $content);
        } else {
            $content = str_replace('{{traits}}', '', $content);
        }

        return $content;
    }

    /**
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
     * @throws Exception
     */
    private function getTableColumns(string $tableName): array
    {
        if (! isset(self::$tableColumns[$tableName])) {
            self::$tableColumns[$tableName] = $this->sm->listTableColumns($tableName);
        }

        return self::$tableColumns[$tableName];
    }

    private function laravelColumnType(Type $type): ?string
    {
        if ($type instanceof BigIntType) {
            return 'int';
        }
        if ($type instanceof DateType) {
            return 'date';
        }
        if ($type instanceof DateTimeType) {
            return 'datetime';
        }
        if ($type instanceof StringType) {
            return 'string';
        }

        return null;
    }
}
