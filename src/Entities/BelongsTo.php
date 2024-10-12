<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class BelongsTo
{
    public function __construct(public ForeignKeyConstraint $foreignKey) {}
}
