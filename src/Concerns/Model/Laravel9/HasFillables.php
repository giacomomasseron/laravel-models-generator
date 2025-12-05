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
            $jsonCasting = config('models-generator.json_casting', [])[$this->entity->name] ?? [];

            $body = $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * The attributes that are mass assignable.'."\n";
            $body .= $this->spacer.' *'."\n";
            $body .= $this->spacer.' * @var list<string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected $fillable = ['."\n";
            foreach ($this->entity->fillable as $fillable) {
                $body .= str_repeat($this->spacer, 2).'\''.$fillable.'\','."\n";
            }
            // We add json casting keys to fillable
            foreach ($jsonCasting as $jsonColumn => $jsonKeys) {
                foreach ($jsonKeys as $jsonKey) {
                    $body .= str_repeat($this->spacer, 2).'\''.$jsonColumn.'->'.$jsonKey.'\','."\n";
                }
            }
            $body .= $this->spacer.'];';

            return $body;
        }

        return '';
    }
}
