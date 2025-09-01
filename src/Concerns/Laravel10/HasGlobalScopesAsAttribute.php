<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel10;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasGlobalScopesAsAttribute
{
    public function globalScopesAsAttribute(): string
    {
        $content = '';

        if (count($this->entity->globalScopes) > 0) {
            $content .= '#[ScopedBy([';
            $content .= implode(', ', array_map(function (string $globalScope) {
                return basename($globalScope).'::class';
            }, $this->entity->globalScopes));
            $content .= '])]'."\n";
        }

        return $content;
    }
}
