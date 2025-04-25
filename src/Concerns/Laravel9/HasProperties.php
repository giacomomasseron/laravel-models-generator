<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasProperties
{
    public function properties(): string
    {
        if (count($this->entity->properties) > 0) {
            return "\n".' *'."\n".implode("\n", array_map(function (Property $property) {
                return ' * @property'.($property->readOnly ? '-read' : '').' '.$property->return.' '.$property->field.(config('models-generator.add_comments_in_phpdocs', true) && ! empty($property->comment) ? " ({$property->comment})" : '');
            }, $this->entity->properties));
        }

        return '';
    }
}
