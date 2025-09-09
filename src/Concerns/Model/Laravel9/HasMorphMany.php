<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasMorphMany
{
    protected function morphMany(): string
    {
        $content = '';

        foreach ($this->entity->morphMany as $key => $morphMany) {
            if ($key !== array_key_first($this->entity->morphMany)) {
                $content .= "\n"."\n";
            }

            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return MorphMany<'.$morphMany->related.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$morphMany->name.'(): MorphMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphMany('.$morphMany->related.'::class, \''.$morphMany->type.'\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
