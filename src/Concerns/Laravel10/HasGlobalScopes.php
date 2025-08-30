<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel10;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasGlobalScopes
{
    public function globalScopes(): string
    {
        $content = '';

        if (count($this->entity->globalScopes) > 0) {
            $content .= '#[ScopedBy([';
            $content .= implode(', ', $this->entity->globalScopes);
            $content .= '])]'."\n";
        }

        return $content;
    }
}
