<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns;

use Doctrine\DBAL\Connection;
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
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\PrimaryKey;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Trait_;
use GiacomoMasseroni\LaravelModelsGenerator\Enums\ColumnTypeEnum;
use GiacomoMasseroni\LaravelModelsGenerator\Factories\DBALVersionFactory;
use GiacomoMasseroni\LaravelModelsGenerator\Helpers\NamingHelper;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

/**
 * @mixin DriverConnectorInterface
 */
trait DBALable
{
    private AbstractSchemaManager $sm;

    private Connection $conn;

    private ?DBALInterface $dbal = null;

    /**
     * @var array<string, mixed>
     */
    private static array $entityColumns = [];

    /**
     * @var array<string, mixed>
     */
    private static array $entityIndexes = [];

    /**
     * @var array<string, list<string>>
     */
    private static array $entityPrimaryKeyColumns = [];

    /**
     * @var array<string, string>
     */
    private array $typeColumnPropertyMaps = [
        'datetime' => 'Carbon',
    ];

    private function dbal(): DBALInterface
    {
        return $this->dbal ??= DBALVersionFactory::create();
    }

    /**
     * @return list<string>
     *
     * @throws Exception
     */
    private function primaryKeyColumns(string $entityName): array
    {
        if (! isset(self::$entityPrimaryKeyColumns[$entityName])) {
            self::$entityPrimaryKeyColumns[$entityName] = $this->dbal()->getPrimaryKeyColumns($this->sm, $entityName);
        }

        return self::$entityPrimaryKeyColumns[$entityName];
    }

    /**
     * @throws Exception
     */
    public function listTables(): array
    {
        return $this->getTables($this->dbal()->listTables($this->sm));
    }

    /**
     * @param  list<\Doctrine\DBAL\Schema\Table>  $tables
     *
     * @return Table[]
     *
     * @throws Exception
     */
    private function getTables(array $tables): array
    {
        /** @var array<string, Table> $dbTables */
        $dbTables = [];

        $morphables = [];

        foreach ($tables as $table) {
            $tableName = $this->dbal()->getTableName($table);
            $fks = $table->getForeignKeys();
            $columns = $this->getEntityColumns($tableName);
            $primaryKeyColumns = $this->primaryKeyColumns($tableName);
            $properties = [];
            $jsonCasting = config('models-generator.json_casting', [])[$tableName] ?? [];

            $dbTable = new Table($tableName, dbEntityNameToModelName($tableName));
            if ($primaryKeyColumns !== []) {
                $primaryKeyName = $primaryKeyColumns[0];
                foreach ($columns as $column) {
                    if ($column->getName() == $primaryKeyName) {
                        $dbTable->primaryKey = new PrimaryKey($primaryKeyName, $column->getAutoincrement(), $this->laravelColumnType($this->mapColumnType($column->getType())));
                    }
                    break;
                }
            }
            $dbTable->fillable = array_filter(
                array_diff(
                    array_keys($columns),
                    array_merge(
                        ['created_at', 'updated_at', 'deleted_at'],
                        $this->getArrayWithPrimaryKey($dbTable)
                    )
                ),
                static function (string $column) use ($jsonCasting): bool {
                    foreach (config('models-generator.exclude_columns', []) as $pattern) {
                        if (@preg_match($pattern, '') === false) {
                            $found = $pattern === $column;
                        } else {
                            $found = (bool) preg_match($pattern, $column);
                        }

                        if ($found) {
                            return false;
                        }
                    }

                    // We add json fillable in HasFillables trait
                    foreach ($jsonCasting as $jsonColumn => $jsonKeys) {
                        if ($jsonColumn === $column) {
                            return false;
                        }
                    }

                    return true;
                }
            );
            if (in_array('password', $dbTable->fillable)) {
                $dbTable->hidden = ['password'];
            }

            $dbTable->connection = $this->connection;

            $dbTable->timestamps = array_key_exists('created_at', $columns) && array_key_exists('updated_at', $columns);
            $dbTable->softDeletes = array_key_exists('deleted_at', $columns);

            /** @var Column $column */
            foreach ($columns as $column) {
                $columnName = $this->dbal()->getColumnName($column);
                $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbTable);
                $dbTable->casts[$columnName] = $this->laravelColumnTypeForCast($this->mapColumnType($column->getType()), $dbTable);

                $properties[] = new Property(
                    '$'.$columnName,
                    ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType).($column->getNotnull() ? '' : '|null'),
                    comment: $column->getComment(),
                    defaultValue: $column->getDefault()
                ); // $laravelColumnType.($column->getNotnull() ? '' : '|null').' $'.$columnName;

                // Get morph
                if (str_ends_with($columnName, '_type') && in_array(str_replace('_type', '', $columnName).'_id', array_keys($columns))) {
                    $dbTable->morphTo[] = new MorphTo(str_replace('_type', '', $columnName));

                    $morphables[str_replace('_type', '', $columnName)] = $dbTable->className;
                }
            }
            $dbTable->properties = $properties;

            // Uuids
            foreach (config('models-generator.uuids') as $tbl => $columns) {
                if ($tbl == $dbTable->name) {
                    $dbTable->uuids = $columns;
                    $dbTable->traits[] = new Trait_(HasUuids::class);
                }
            }

            // Ulids
            foreach (config('models-generator.ulids') as $tbl) {
                if ($tbl == $dbTable->name) {
                    $dbTable->ulids[] = $dbTable->name;
                    $dbTable->traits[] = new Trait_(HasUlids::class);
                }
            }

            // Table traits
            foreach (config('models-generator.table_traits', []) as $tbl => $traits) {
                if ($tbl == $dbTable->name) {
                    if (is_array($traits)) {
                        foreach ($traits as $trait) {
                            $dbTable->traits[] = new Trait_($trait);
                        }
                    } else {
                        $dbTable->traits[] = new Trait_($traits);
                    }
                }
            }

            // Observers
            foreach (config('models-generator.observers', []) as $tbl => $observer) {
                if ($tbl == $dbTable->name) {
                    $dbTable->observer = $observer;
                }
            }

            // Query builders
            foreach (config('models-generator.query_builders', []) as $tbl => $queryBuilder) {
                if ($tbl == $dbTable->name) {
                    $dbTable->queryBuilder = $queryBuilder;
                }
            }

            // Global scopes
            foreach (config('models-generator.global_scopes', []) as $tbl => $globalScopes) {
                if ($tbl == $dbTable->name) {
                    $dbTable->globalScopes = $globalScopes;
                }
            }

            foreach ($fks as $fk) {
                if (isRelationshipToBeAdded($dbTable->name, $dbTable->dbalVersion->getForeignTableName($fk))) {
                    $dbTable->addBelongsTo(new BelongsTo($fk));
                }
            }

            if (resolveLaravelVersion()->check(13)) {
                $dbTable->imports[] = 'Illuminate\Database\Eloquent\Attributes\Table';

                if (count($dbTable->fillable) > 0) {
                    $dbTable->imports[] = 'Illuminate\Database\Eloquent\Attributes\Fillable';
                }

                if (count($dbTable->hidden) > 0) {
                    $dbTable->imports[] = 'Illuminate\Database\Eloquent\Attributes\Hidden';
                }

                if ($dbTable->showConnectionProperty && ! empty($dbTable->connection)) {
                    $dbTable->imports[] = 'Illuminate\Database\Eloquent\Attributes\Connection';
                }
            }

            $dbTables[$tableName] = $dbTable;
        }

        foreach ($dbTables as $dbTable) {
            foreach ($dbTable->belongsTo as $foreignName => $belongsTo) {
                $foreignTableName = $dbTable->dbalVersion->getForeignTableName($belongsTo->foreignKey);
                $foreignKeyName = $dbTable->dbalVersion->getLocalColumns($belongsTo->foreignKey)[0];
                $localKeyName = $dbTable->dbalVersion->getForeignColumns($belongsTo->foreignKey)[0];
                if ($localKeyName == $dbTables[$foreignTableName]->primaryKey) {
                    $localKeyName = null;
                }
                if (isRelationshipToBeAdded($dbTable->name, $foreignTableName)) {
                    $dbTables[$foreignTableName]->addHasMany(new HasMany($dbTable->className, $foreignKeyName, $localKeyName));
                }

                if (count($dbTable->belongsTo) > 1) {
                    foreach ($dbTable->belongsTo as $subForeignName => $subBelongsTo) {
                        $subForeignTableName = $dbTable->dbalVersion->getForeignTableName($subBelongsTo->foreignKey);

                        if ($foreignTableName != $subForeignTableName) {
                            if (isRelationshipToBeAdded($dbTable->name, $subForeignTableName)) {
                                $tablePrimaryKey = $this->primaryKeyColumns($dbTables[$foreignTableName]->name);
                                $relatedPrimaryKey = $this->primaryKeyColumns($subForeignTableName);
                                $pivotPrimaryKeyColumns = $this->primaryKeyColumns($dbTable->name);

                                if ($tablePrimaryKey === [] || $relatedPrimaryKey === []) {
                                    continue;
                                }

                                $foreignPivotKey = $tablePrimaryKey[0];
                                $relatedPivotKey = $relatedPrimaryKey[0];
                                $pivotPrimaryKey = $pivotPrimaryKeyColumns[0] ?? null;

                                $pivotColumns = $this->getEntityColumns($dbTable->name);
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
                            }
                        }
                    }
                }
            }

            // Morph many
            foreach (config('models-generator.morphs') as $table => $relationship) {
                if ($table == $dbTable->name) {
                    $dbTable->morphMany[] = new MorphMany(
                        NamingHelper::caseRelationName(Str::plural($morphables[$relationship])),
                        $morphables[$relationship],
                        $relationship,
                    );
                }
            }
        }

        return $dbTables;
    }

    public function laravelColumnTypeForCast(ColumnTypeEnum $type, ?Entity $dbTable = null): string
    {
        return match ($type) {
            ColumnTypeEnum::INT => 'integer',
            ColumnTypeEnum::DATETIME => 'datetime',
            ColumnTypeEnum::FLOAT => 'float',
            ColumnTypeEnum::BOOLEAN => 'boolean',
            default => 'string',
        };
    }

    public function laravelColumnType(ColumnTypeEnum $type, ?Entity $dbTable = null): string
    {
        if ($type == ColumnTypeEnum::INT) {
            return 'int';
        }
        if ($type == ColumnTypeEnum::DATETIME) {
            if ($dbTable !== null) {
                $dbTable->imports[] = 'Carbon\Carbon';
            }

            return 'datetime';
        }
        if ($type == ColumnTypeEnum::STRING) {
            return 'string';
        }
        if ($type == ColumnTypeEnum::FLOAT) {
            return 'float';
        }
        if ($type == ColumnTypeEnum::BOOLEAN) {
            return 'bool';
        }

        return 'string';
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getEntityColumns(string $entityName): array
    {
        if (! isset(self::$entityColumns[$entityName])) {
            self::$entityColumns[$entityName] = $this->dbal()->listTableColumns($this->sm, $entityName);
        }

        return self::$entityColumns[$entityName];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getEntityIndexes(string $entityName): array
    {
        if (! isset(self::$entityIndexes[$entityName])) {
            self::$entityIndexes[$entityName] = $this->dbal()->listTableIndexes($this->sm, $entityName);
        }

        return self::$entityIndexes[$entityName];
    }

    private function mapColumnType(Type $type): ColumnTypeEnum
    {
        if ($type instanceof SmallIntType ||
            $type instanceof BigIntType ||
            $type instanceof IntegerType
        ) {
            return ColumnTypeEnum::INT;
        }
        if ($type instanceof DateType ||
            $type instanceof DateTimeType ||
            $type instanceof DateImmutableType ||
            $type instanceof DateTimeImmutableType ||
            $type instanceof DateTimeTzType ||
            $type instanceof DateTimeTzImmutableType
        ) {
            return ColumnTypeEnum::DATETIME;
        }
        if ($type instanceof StringType ||
            $type instanceof TextType) {
            return ColumnTypeEnum::STRING;
        }
        if ($type instanceof DecimalType ||
            $type instanceof SmallFloatType ||
            $type instanceof FloatType
        ) {
            return ColumnTypeEnum::FLOAT;
        }
        if ($type instanceof BooleanType) {
            return ColumnTypeEnum::BOOLEAN;
        }

        return ColumnTypeEnum::STRING;
    }

    /**
     * @return list<string>
     */
    private function getArrayWithPrimaryKey(Table $dbTable): array
    {
        return $dbTable->primaryKey !== null ? (config('models-generator.primary_key_in_fillable', false) && ! empty($dbTable->primaryKey->name) ? [] : [$dbTable->primaryKey->name]) : [];
    }
}
