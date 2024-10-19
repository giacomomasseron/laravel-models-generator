<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\RelationshipInterface;

class BelongsTo implements RelationshipInterface
{
    public function __construct(public ForeignKeyConstraint $foreignKey) {}
}
