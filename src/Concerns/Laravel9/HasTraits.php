<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasTraits
{
    public function traits(): string
    {
        $traitsToUse = $this->entity->traits;
        if ($this->entity->softDeletes) {
            $traitsToUse[] = 'SoftDeletes';
        }
        if (count($traitsToUse) > 0) {
            asort($traitsToUse);

            $body = '';
            foreach ($traitsToUse as $key => $trait) {
                $parts = explode('\\', $trait);
                $body .= $this->spacer.'use '.end($parts).';';
                if ($key !== array_key_last($traitsToUse)) {
                    $body .= "\n";
                }
            }

            return $body;
        }

        return '';
    }
}
