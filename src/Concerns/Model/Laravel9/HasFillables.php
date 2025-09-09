<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasFillables
{
    public function fillable(): string
    {
        if (count($this->entity->fillable) > 0) {
            $body = $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * The attributes that are mass assignable.'."\n";
            $body .= $this->spacer.' *'."\n";
            $body .= $this->spacer.' * @var list<string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected $fillable = ['."\n";
            foreach ($this->entity->fillable as $fillable) {
                $body .= str_repeat($this->spacer, 2).'\''.$fillable.'\','."\n";
            }
            $body .= $this->spacer.'];';

            return $body;
        }

        return '';
    }
}
