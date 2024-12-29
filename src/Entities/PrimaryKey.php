<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

class PrimaryKey
{
    public function __construct(
        public string $name,
        public bool $autoIncrement,
        public ?string $keyType,
    ) {}
}
