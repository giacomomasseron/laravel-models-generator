<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasUuids
{
    public function uuids(): string
    {
        $content = '';

        if (count($this->entity->uuids) == 0 || (count($this->entity->uuids) == 1 && $this->entity->uuids[0] == 'id')) {
            return $content;
        }

        $content .= $this->spacer.'/**'."\n";
        $content .= $this->spacer.' * @return array<int, string>'."\n";
        $content .= $this->spacer.' */'."\n";
        $content .= $this->spacer.'public function uniqueIds(): array'."\n";
        $content .= $this->spacer.'{'."\n";
        $content .= str_repeat($this->spacer, 2)."return ['".implode("', '", $this->entity->uuids)."'];"."\n";
        $content .= $this->spacer.'}';

        return $content;
    }
}
