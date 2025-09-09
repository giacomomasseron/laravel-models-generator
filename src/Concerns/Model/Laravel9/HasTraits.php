<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Trait_;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasTraits
{
    public function traits(): string
    {
        $traitsToUse = array_map(function (Trait_ $trait) {
            $parts = explode('\\', $trait->value);

            return new Trait_(end($parts), $trait->phpDoc);
        }, $this->entity->traits);

        if ($this->entity->softDeletes) {
            $traitsToUse[] = new Trait_('SoftDeletes');
        }
        if (count($traitsToUse) > 0) {
            usort($traitsToUse, function (Trait_ $a, Trait_ $b) {
                return strcmp($a->value, $b->value);
            });

            $body = '';
            foreach ($traitsToUse as $key => $trait) {
                // $parts = explode('\\', $trait);
                if (! is_null($trait->phpDoc)) {
                    $body .= ($key !== array_key_first($traitsToUse) ? "\n" : '').$this->spacer.$trait->phpDoc."\n";
                }
                // $body .= $this->spacer.'use '.end($parts).';';
                $body .= $this->spacer.'use '.$trait->value.';';

                if (! is_null($trait->phpDoc)) {
                    if ($key !== array_key_last($traitsToUse)) {
                        $body .= "\n";
                    }
                }

                if ($key !== array_key_last($traitsToUse)) {
                    $body .= "\n";
                }
            }

            return $body;
        }

        return '';
    }
}
