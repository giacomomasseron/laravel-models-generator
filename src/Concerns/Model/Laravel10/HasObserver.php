<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel10;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

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
