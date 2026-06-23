<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Contracts;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

interface DBALInterface
{
    public function getForeignTableName(ForeignKeyConstraint $foreignKeyConstraint): string;

    /**
     * @return non-empty-list<string>
     */
    public function getLocalColumns(ForeignKeyConstraint $foreignKeyConstraint): array;

    /**
     * @return non-empty-array<int, string>
     */
    public function getForeignColumns(ForeignKeyConstraint $foreignKeyConstraint): array;

    /**
     * @param  AbstractSchemaManager<AbstractPlatform>  $schemaManager
     *
     * @return list<Table>
     */
    public function listTables(AbstractSchemaManager $schemaManager): array;

    /**
     * @param  AbstractSchemaManager<AbstractPlatform>  $schemaManager
     *
     * @return array<string, Column>
     */
    public function listTableColumns(AbstractSchemaManager $schemaManager, string $table): array;

    /**
     * @param  AbstractSchemaManager<AbstractPlatform>  $schemaManager
     *
     * @return array<string, Index>
     */
    public function listTableIndexes(AbstractSchemaManager $schemaManager, string $table): array;

    /**
     * Names of the columns composing the table's primary key (empty if there is none).
     *
     * @param  AbstractSchemaManager<AbstractPlatform>  $schemaManager
     *
     * @return list<string>
     */
    public function getPrimaryKeyColumns(AbstractSchemaManager $schemaManager, string $table): array;

    public function getTableName(Table $table): string;

    public function getColumnName(Column $column): string;

    public function getForeignKeyName(ForeignKeyConstraint $foreignKeyConstraint): string;
}
