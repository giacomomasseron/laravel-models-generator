<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

class Property
{
    public function __construct(
        public string $field,
        public string $return,
        public bool $readOnly = false,
        public ?string $comment = null,
        public mixed $defaultValue = null,
    ) {}
}
