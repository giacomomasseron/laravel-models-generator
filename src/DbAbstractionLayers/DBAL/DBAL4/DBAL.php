<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers\DBAL\DBAL4;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Table;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;

class DBAL implements DBALInterface
{
    public function getForeignTableName(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        return $foreignKeyConstraint->getReferencedTableName()->getUnqualifiedName()->getValue();
    }

    public function getLocalColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        return array_map(fn (UnqualifiedName $unqualifiedName) => $unqualifiedName->getIdentifier()->getValue(), $foreignKeyConstraint->getReferencingColumnNames());
    }

    public function getForeignColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        return array_map(fn (UnqualifiedName $unqualifiedName) => $unqualifiedName->getIdentifier()->getValue(), $foreignKeyConstraint->getReferencedColumnNames());
    }

    public function listTables(AbstractSchemaManager $schemaManager): array
    {
        return $schemaManager->introspectTables();
    }

    public function listTableColumns(AbstractSchemaManager $schemaManager, string $table): array
    {
        if ($table === '') {
            return [];
        }

        $columns = [];
        // introspectTableColumnsByUnquotedName() only lists columns, so it also works for views
        // (unlike introspectTableByUnquotedName(), which builds a full table definition and throws on a view).
        foreach ($schemaManager->introspectTableColumnsByUnquotedName($table) as $column) {
            $columns[strtolower($this->getColumnName($column))] = $column;
        }

        return $columns;
    }

    public function listTableIndexes(AbstractSchemaManager $schemaManager, string $table): array
    {
        if ($table === '') {
            return [];
        }

        return $schemaManager->introspectTableByUnquotedName($table)->getIndexes();
    }

    public function getPrimaryKeyColumns(AbstractSchemaManager $schemaManager, string $table): array
    {
        if ($table === '') {
            return [];
        }

        $primaryKey = $schemaManager->introspectTableByUnquotedName($table)->getPrimaryKeyConstraint();

        if ($primaryKey === null) {
            return [];
        }

        return array_map(
            fn (UnqualifiedName $unqualifiedName) => $unqualifiedName->getIdentifier()->getValue(),
            $primaryKey->getColumnNames()
        );
    }

    public function getTableName(Table $table): string
    {
        return $table->getObjectName()->getUnqualifiedName()->getValue();
    }

    public function getColumnName(Column $column): string
    {
        return $column->getObjectName()->getIdentifier()->getValue();
    }

    public function getForeignKeyName(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        return $foreignKeyConstraint->getObjectName()?->getIdentifier()->getValue() ?? '';
    }
}
