<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer;

/**
 * @mixin Writer
 */
trait HasBelongsToMany
{
    protected function belongsToMany(): string
    {
        $content = '';

        foreach ($this->entity->belongsToMany as $key => $belongsToMany) {
            if ($key !== array_key_first($this->entity->belongsToMany)) {
                $content .= "\n"."\n";
            }

            $withPivot = count($belongsToMany->pivotAttributes);

            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return BelongsToMany<'.$belongsToMany->foreignClassName.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$belongsToMany->name.'(): BelongsToMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsToMany('.$belongsToMany->foreignClassName.'::class, \''.$belongsToMany->pivot.'\', \''.$belongsToMany->foreignPivotKey.'\', \''.$belongsToMany->relatedPivotKey.'\')'.(! $withPivot && ! $belongsToMany->timestamps ? ';' : '')."\n";
            $content .= $withPivot ? str_repeat($this->spacer, 3).(count($belongsToMany->pivotAttributes) > 0 ? '->withPivot(\''.implode('\', \'', $belongsToMany->pivotAttributes).'\')' : '').(! $belongsToMany->timestamps ? ';' : '')."\n" : '';
            $content .= $belongsToMany->timestamps ? str_repeat($this->spacer, 3).'->withTimestamps();'."\n" : '';
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
