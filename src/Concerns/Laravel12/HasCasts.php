<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel12;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasCasts
{
    public function casts(): string
    {
        $body = '';

        if (count($this->entity->casts) > 0) {
            $uriCasting = config('models-generator.uri_casting', [])[$this->entity->name] ?? [];

            $body .= $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * @return array<string, string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected function casts(): array'."\n";
            $body .= $this->spacer.'{'."\n";
            $body .= str_repeat($this->spacer, 2).'return ['."\n";
            foreach ($this->entity->casts as $column => $type) {
                if (array_key_exists($column, (array) config('models-generator.enums_casting', []))) {
                    $type = '\\'.config('models-generator.enums_casting', [])[$column].'::class';
                } elseif (in_array($column, $uriCasting)) {
                    $type = '\\'.'Illuminate\Database\Eloquent\Casts\AsUri::class';
                } else {
                    $type = '\''.$type.'\'';
                }
                $body .= str_repeat($this->spacer, 3).'\''.$column.'\' => '.$type.','."\n";
            }
            $body .= str_repeat($this->spacer, 2).'];'."\n";
            $body .= $this->spacer.'}';

            return $body;
        }

        return '';
    }
}
