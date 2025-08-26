<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers\DBAL\DBAL3;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
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
}
