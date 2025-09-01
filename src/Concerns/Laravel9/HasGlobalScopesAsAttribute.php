<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasGlobalScopesAsAttribute
{
    public function globalScopesAsAttribute(): string
    {
        return '';
    }
}
