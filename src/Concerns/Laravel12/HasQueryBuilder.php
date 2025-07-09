<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel12;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasQueryBuilder
{
    public function queryBuilder(): string
    {
        if (! is_null($this->entity->queryBuilder)) {
            return '#[UseEloquentBuilder('.basename($this->entity->queryBuilder).'::class)]'."\n";
        }

        return '';
    }
}
