<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel11;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasBelongTo
{
    protected function belongTo(): string
    {
        $content = '';

        foreach ($this->entity->belongsTo as $key => $belongsTo) {
            if ($key !== array_key_first($this->entity->belongsTo)) {
                $content .= "\n"."\n";
            }

            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return BelongsTo<'.$belongsTo->foreignClassName.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$belongsTo->name.'(): BelongsTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsTo('.$belongsTo->foreignClassName.'::class, \''.$belongsTo->localColumnName.'\''.(($this->entity->primaryKey->name ?? '') != $belongsTo->foreignColumnName ? ', \''.$belongsTo->foreignColumnName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
