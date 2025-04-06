<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel12;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\WriterInterface;
use Illuminate\Support\Str;

class Writer extends \GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer implements WriterInterface
{
    public function imports(): string
    {
        asort($this->entity->imports);

        return implode("\n", array_map(function (string $import) {
            return "use $import;";
        }, array_unique($this->entity->imports)));
    }

    public function properties(): string
    {
        if (count($this->entity->properties) > 0) {
            $this->prevElementWasNotEmpty = true;

            return "\n".' *'."\n".implode("\n", array_map(function (Property $property) {
                return ' * @property'.($property->readOnly ? '-read' : '').' '.$property->return.' '.$property->field.(config('models-generator.add_comments_in_phpdocs', true) && ! empty($property->comment) ? " ({$property->comment})" : '');
            }, $this->entity->properties));
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function parent(): string
    {
        $parent = $this->entity->parent ?? 'Model';

        if (count($this->entity->interfaces) > 0) {
            $this->prevElementWasNotEmpty = true;

            asort($this->entity->interfaces);

            $parent .= ' implements '.implode(', ', array_map(function (string $interface) {
                $parts = explode('\\', $interface);

                return end($parts);
            }, $this->entity->interfaces));

            return $parent;
        }

        $this->prevElementWasNotEmpty = false;

        return $parent;
    }

    public function primaryKey(): string
    {
        $body = '';
        $this->prevElementWasNotEmpty = false;

        if (config('models-generator.primary_key')) {
            $this->prevElementWasNotEmpty = true;

            if ($this->entity->primaryKey !== null) {
                $body = "\n".$this->spacer.'protected $primaryKey = \''.$this->entity->primaryKey->name.'\';';

                if (! $this->entity->primaryKey->autoIncrement) {
                    $body .= "\n"."\n".$this->spacer.'public $incrementing = false;'."\n"."\n";
                    $body .= $this->spacer.'protected $keyType = \'string\';';
                }
            } else {
                $body = "\n".$this->spacer.'protected $primaryKey = null;'."\n"."\n";
                $body .= $this->spacer.'public $incrementing = false;';
            }
        }

        return $body;
    }

    public function casts(): string
    {
        $body = '';

        if (count($this->entity->casts) > 0) {
            $this->prevElementWasNotEmpty = true;

            $body .= "\n"."\n".$this->spacer.'/**'."\n";
            $body .= $this->spacer.' * @return array<string, string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected function casts(): array'."\n";
            $body .= $this->spacer.'{'."\n";
            $body .= str_repeat($this->spacer, 2).'return ['."\n";
            foreach ($this->entity->casts as $column => $type) {
                if (array_key_exists($column, (array) config('models-generator.enums_casting', []))) {
                    $type = '\\'.config('models-generator.enums_casting', [])[$column].'::class';
                } else {
                    $type = '\''.$type.'\'';
                }
                $body .= str_repeat($this->spacer, 3).'\''.$column.'\' => '.$type.','."\n";
            }
            $body .= str_repeat($this->spacer, 2).'];'."\n";
            $body .= $this->spacer.'}';

            return $body;
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function relationships(): string
    {
        $body = $this->hasMany();
        $body .= $this->belongTo();
        $body .= $this->belongsToMany();
        $body .= $this->morphTo();
        $body .= $this->morphMany();

        return $body;
    }

    public function fillable(): string
    {
        if (count($this->entity->fillable) > 0) {
            $this->prevElementWasNotEmpty = true;

            $body = "\n"."\n".$this->spacer.'/**'."\n";
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

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function hidden(): string
    {
        if (count($this->entity->hidden) > 0) {
            $this->prevElementWasNotEmpty = true;

            $body = "\n"."\n".$this->spacer.'protected $hidden = ['."\n";
            foreach ($this->entity->hidden as $hidden) {
                $body .= str_repeat($this->spacer, 2).'\''.$hidden.'\','."\n";
            }
            $body .= $this->spacer.'];';

            return $body;
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function traits(): string
    {
        $traitsToUse = $this->entity->traits;
        if ($this->entity->softDeletes) {
            $traitsToUse[] = 'SoftDeletes';
        }
        if (count($traitsToUse) > 0) {
            $this->prevElementWasNotEmpty = true;

            asort($traitsToUse);

            $body = '';
            foreach ($traitsToUse as $trait) {
                $parts = explode('\\', $trait);
                $body .= "\n".$this->spacer.'use '.end($parts).';';
            }

            // $body .= "\n";

            return $body;
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    private function hasMany(): string
    {
        $content = '';
        foreach ($this->entity->hasMany as $hasMany) {
            $this->prevElementWasNotEmpty = true;

            $relatedClassName = ucfirst(Str::camel($hasMany->related));

            $content .= "\n"."\n";

            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return HasMany<'.$relatedClassName.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$hasMany->name.'(): HasMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->hasMany('.$relatedClassName.'::class, \''.$hasMany->foreignKeyName.'\''.(! empty($hasMany->localKeyName) ? ', \''.$hasMany->localKeyName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        $this->prevElementWasNotEmpty = false;

        return $content;
    }

    private function belongTo(): string
    {
        $content = '';
        foreach ($this->entity->belongsTo as $belongsTo) {
            $this->prevElementWasNotEmpty = true;

            $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return BelongsTo<'.$belongsTo->foreignClassName.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$belongsTo->name.'(): BelongsTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsTo('.$belongsTo->foreignClassName.'::class, \''.$belongsTo->localColumnName.'\''.(($this->entity->primaryKey->name ?? '') != $belongsTo->foreignColumnName ? ', \''.$belongsTo->foreignColumnName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        $this->prevElementWasNotEmpty = false;

        return $content;
    }

    private function belongsToMany(): string
    {
        $content = '';
        $this->prevElementWasNotEmpty = false;

        foreach ($this->entity->belongsToMany as $belongsToMany) {
            $this->prevElementWasNotEmpty = true;

            $withPivot = count($belongsToMany->pivotAttributes);

            $content .= "\n"."\n";
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

    private function morphTo(): string
    {
        $content = '';
        foreach ($this->entity->morphTo as $morphTo) {
            $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return MorphTo<Model, $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$morphTo->name.'(): MorphTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphTo(__FUNCTION__, \''.$morphTo->name.'_type\', \''.$morphTo->name.'_id\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }

    private function morphMany(): string
    {
        $content = '';
        foreach ($this->entity->morphMany as $morphMany) {
            $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return MorphMany<'.$morphMany->related.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$morphMany->name.'(): MorphMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphMany('.$morphMany->related.'::class, \''.$morphMany->type.'\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }

    public function abstract(): string
    {
        return $this->entity->abstract ? 'abstract ' : '';
    }
}
