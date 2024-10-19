<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\RelationshipInterface;

class MorphTo implements RelationshipInterface
{
    public function __construct(
        public string $name,
    ) {}
}
