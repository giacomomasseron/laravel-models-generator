<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

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
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return void'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'protected static function booted()'."\n";
            $content .= $this->spacer.'{'."\n";

            foreach ($this->entity->globalScopes as $globalScope) {
                $content .= str_repeat($this->spacer, 2).'static::addGlobalScope(new '.$globalScope.'());'."\n";
            }

            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
