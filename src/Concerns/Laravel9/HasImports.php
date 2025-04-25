<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasImports
{
    public function imports(): string
    {
        asort($this->entity->imports);

        return implode("\n", array_map(function (string $import) {
            return "use $import;";
        }, array_unique($this->entity->imports)));
    }
}
