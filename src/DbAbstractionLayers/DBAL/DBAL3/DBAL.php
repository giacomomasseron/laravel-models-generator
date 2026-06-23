<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers\DBAL\DBAL3;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;

class DBAL implements DBALInterface
{
    public function getForeignTableName(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        // @phpstan-ignore-next-line
        return $foreignKeyConstraint->getForeignTableName();
    }

    public function getLocalColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        // @phpstan-ignore-next-line
        return $foreignKeyConstraint->getLocalColumns();
    }

    public function getForeignColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        // @phpstan-ignore-next-line
        return $foreignKeyConstraint->getForeignColumns();
    }

    public function listTables(AbstractSchemaManager $schemaManager): array
    {
        // @phpstan-ignore-next-line
        return $schemaManager->listTables();
    }

    public function listTableColumns(AbstractSchemaManager $schemaManager, string $table): array
    {
        // @phpstan-ignore-next-line
        return $schemaManager->listTableColumns($table);
    }

    public function listTableIndexes(AbstractSchemaManager $schemaManager, string $table): array
    {
        // @phpstan-ignore-next-line
        return $schemaManager->listTableIndexes($table);
    }

    public function getPrimaryKeyColumns(AbstractSchemaManager $schemaManager, string $table): array
    {
        // @phpstan-ignore-next-line
        $indexes = $schemaManager->listTableIndexes($table);

        if (! isset($indexes['primary'])) {
            return [];
        }

        // @phpstan-ignore-next-line
        return array_values($indexes['primary']->getColumns());
    }

    public function getTableName(Table $table): string
    {
        // @phpstan-ignore-next-line
        return $table->getName();
    }

    public function getColumnName(Column $column): string
    {
        // @phpstan-ignore-next-line
        return $column->getName();
    }

    public function getForeignKeyName(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        // @phpstan-ignore-next-line
        return $foreignKeyConstraint->getName();
    }
}
