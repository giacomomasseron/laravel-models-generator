<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasAbstract
{
    public function abstract(): string
    {
        return $this->entity->abstract ? 'abstract ' : '';
    }
}
