<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasDefaultValues
{
    public function defaultValues(): string
    {
        if (config('models-generator.attributes') && count($this->entity->properties) > 0) {
            $body = $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * The model\'s default values for attributes.'."\n";
            $body .= $this->spacer.' *'."\n";
            $body .= $this->spacer.' * @var array<string, mixed>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected $attributes = ['."\n";
            foreach ($this->entity->properties as $property) {
                if (! is_null($property->defaultValue)) {
                    $body .= str_repeat($this->spacer, 2).'\''.$property->field.'\' => \''.$property->defaultValue.'\','."\n";
                }
            }
            $body .= $this->spacer.'];';

            return $body;
        }

        return '';
    }
}
