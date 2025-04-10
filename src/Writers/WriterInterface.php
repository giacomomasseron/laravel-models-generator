<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers;

interface WriterInterface
{
    public function writeModelFile(): string;

    public function namespace(): string;

    public function parent(): string;

    public function traits(): string;

    public function abstract(): string;

    public function table(): string;

    public function primaryKey(): string;

    public function timestamps(): string;

    public function fillable(): string;

    public function hidden(): string;

    public function imports(): string;

    public function properties(): string;

    public function rules(): string;

    public function casts(): string;

    public function relationships(): string;
}
