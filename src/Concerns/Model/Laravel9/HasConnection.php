<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasConnection
{
    public function connection(): string
    {
        $body = '';

        if ($this->entity->showConnectionProperty && ! empty($this->entity->connection)) {
            $body .= $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * @var string'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected $connection = \''.$this->entity->connection.'\';';

            return $body;
        }

        return $body;
    }
}
