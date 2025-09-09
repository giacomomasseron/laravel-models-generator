<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel11;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasMorphTo
{
    protected function morphTo(): string
    {
        $content = '';
        foreach ($this->entity->morphTo as $key => $morphTo) {
            if ($key !== array_key_first($this->entity->morphTo)) {
                $content .= "\n"."\n";
            }

            // $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return MorphTo<Model, $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$morphTo->name.'(): MorphTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphTo(__FUNCTION__, \''.$morphTo->name.'_type\', \''.$morphTo->name.'_id\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
