<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Table;

abstract class Writer implements WriterInterface
{
    public string $spacer = '    ';

    public function __construct(public string $className, public Table $table, public string $stubContent) {}

    public function writeModelFile(): string
    {
        $search = [
            '{{strict}}',
            '{{namespace}}',
            '{{properties}}',
            '{{class}}',
            '{{imports}}',
            '{{parent}}',
            '{{body}}',
        ];
        $replace = [
            $this->strict(),
            $this->namespace(),
            $this->properties(),
            $this->className,
            $this->imports(),
            $this->parent(),
            $this->body(),
        ];

        return str_replace($search, $replace, $this->stubContent);
    }

    abstract public function traits(): string;

    abstract public function table(): string;

    abstract public function primaryKey(): string;

    abstract public function timestamps(): string;

    abstract public function fillable(): string;

    abstract public function hidden(): string;

    abstract public function imports(): string;

    abstract public function properties(): string;

    abstract public function casts(): string;

    abstract public function relationships(): string;

    abstract public function body(): string;

    abstract public function parent(): string;

    public function namespace(): string
    {
        return (string) config('models-generator.namespace', 'App\Models');
    }

    public function strict(): string
    {
        return (bool) config('models-generator.strict_types', true) ? ' declare(strict_types=1);' : '';
    }
}
