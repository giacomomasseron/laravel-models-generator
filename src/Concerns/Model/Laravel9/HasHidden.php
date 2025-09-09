<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasHidden
{
    public function hidden(): string
    {
        if (count($this->entity->hidden) > 0) {
            $body = $this->spacer.'protected $hidden = ['."\n";
            foreach ($this->entity->hidden as $hidden) {
                $body .= str_repeat($this->spacer, 2).'\''.$hidden.'\','."\n";
            }
            $body .= $this->spacer.'];';

            return $body;
        }

        return '';
    }
}
