<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory;

interface WriterInterface
{
    public function writeFactoryFile(): string;

    public function fields(): string;
}
