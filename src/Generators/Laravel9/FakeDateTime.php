<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Generators\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\FakeGeneratorInterface;

class FakeDateTime implements FakeGeneratorInterface
{
    public function __toString(): string
    {
        return 'fake()->dateTime';
    }
}
