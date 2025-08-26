<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers\DBAL\DBAL4;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;

class DBAL implements DBALInterface
{
    public function getForeignTableName(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        return $foreignKeyConstraint->getReferencedTableName()->getUnqualifiedName()->toString();
    }

    public function getLocalColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        return array_map(fn (UnqualifiedName $unqualifiedName) => $unqualifiedName->toString(), $foreignKeyConstraint->getReferencingColumnNames());
    }

    public function getForeignColumns(ForeignKeyConstraint $foreignKeyConstraint): array
    {
        return array_map(fn (UnqualifiedName $unqualifiedName) => $unqualifiedName->toString(), $foreignKeyConstraint->getReferencedColumnNames());
    }
}
