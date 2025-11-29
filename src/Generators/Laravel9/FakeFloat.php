<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Generators\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\FakeGeneratorInterface;
use Random\RandomException;

class FakeFloat implements FakeGeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function __toString(): string
    {
        return 'fake()->randomFloat(2, 1, '.random_int(2, 10000).')';
    }
}
