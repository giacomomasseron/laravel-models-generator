<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Generators\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\FakeGeneratorInterface;

class FakeAddress implements FakeGeneratorInterface
{
    public function __toString(): string
    {
        return 'fake()->address';
    }
}
