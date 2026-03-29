<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel13;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasHidden
{
    public function hidden(): string
    {
        return '';
    }
}
