<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

class Trait_
{
    public function __construct(
        public string $value,
        public ?string $phpDoc = null,
    ) {}
}
