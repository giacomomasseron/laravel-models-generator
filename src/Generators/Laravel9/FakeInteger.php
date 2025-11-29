<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Generators\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\FakeGeneratorInterface;
use Random\RandomException;

class FakeInteger implements FakeGeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function __toString(): string
    {
        return 'fake()->numberBetween(1, '.random_int(2, 10000).')';
    }
}
