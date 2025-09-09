<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasParent
{
    public function parent(): string
    {
        $parent = $this->entity->parent ?? 'Model';

        if (count($this->entity->interfaces) > 0) {
            asort($this->entity->interfaces);

            $parent .= ' implements '.implode(', ', array_map(function (string $interface) {
                $parts = explode('\\', $interface);

                return end($parts);
            }, $this->entity->interfaces));

            return $parent;
        }

        return $parent;
    }
}
