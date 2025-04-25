<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;
use Illuminate\Support\Str;

/**
 * @mixin Writer
 */
trait HasHasMany
{
    protected function hasMany(): string
    {
        $content = '';
        foreach ($this->entity->hasMany as $key => $hasMany) {
            if ($key !== array_key_first($this->entity->hasMany)) {
                $content .= "\n"."\n";
            }

            $relatedClassName = ucfirst(Str::camel($hasMany->related));

            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return HasMany<'.$relatedClassName.'>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$hasMany->name.'(): HasMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->hasMany('.$relatedClassName.'::class, \''.$hasMany->foreignKeyName.'\''.(! empty($hasMany->localKeyName) ? ', \''.$hasMany->localKeyName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
