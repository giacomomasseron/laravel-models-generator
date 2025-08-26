<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Contracts;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;

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
}
