<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;

abstract class Writer implements WriterInterface
{
    public string $spacer = '    ';

    public function __construct(
        public string $className,
        public Entity $entity,
        public string $stubContent,
    ) {}

    public function writeFactoryFile(): string
    {
        $search = [
            '{{namespace}}',
            '{{className}}',
            '{{fields}}',
        ];
        $replace = [
            (string) config('models-generator.namespace', 'App\Models'),
            $this->className,
            $this->fields(),
        ];

        return str_replace($search, $replace, $this->stubContent);
    }

    abstract public function fields(): string;
}
