<?php

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel10;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasObserver
{
    public function observer(): string
    {
        if (! is_null($this->entity->observer)) {
            return '#[ObservedBy(['.basename($this->entity->observer).'::class])]'."\n";
        }

        return '';
    }
}
