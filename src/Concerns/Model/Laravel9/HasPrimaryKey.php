<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasPrimaryKey
{
    public function primaryKey(): string
    {
        $body = '';

        if (config('models-generator.primary_key')) {
            if ($this->entity->primaryKey !== null) {
                $body .= $this->spacer.'protected $primaryKey = \''.$this->entity->primaryKey->name.'\';';

                if (! $this->entity->primaryKey->autoIncrement) {
                    $body .= "\n"."\n".$this->spacer.'public $incrementing = false;'."\n"."\n";
                    $body .= $this->spacer.'protected $keyType = \'string\';';
                }
            }/* else {
                $body .= $this->spacer.'protected $primaryKey = null;'."\n"."\n";
                $body .= $this->spacer.'public $incrementing = false;';
            }*/

            return $body;
        }

        return $body;
    }
}
