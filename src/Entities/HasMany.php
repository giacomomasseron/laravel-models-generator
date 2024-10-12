<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

class HasMany
{
    public function __construct(
        public string $name,
        public string $foreignKeyName,
        public ?string $localKeyName = null
    ) {}
}
