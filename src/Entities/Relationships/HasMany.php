<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\RelationshipInterface;

class HasMany implements RelationshipInterface
{
    public function __construct(
        public string $name,
        public string $foreignKeyName,
        public ?string $localKeyName = null
    ) {}
}
