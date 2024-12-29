<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel11;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\WriterInterface;
use Illuminate\Support\Str;

class Writer extends \GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer implements WriterInterface
{
    public function imports(): string
    {
        asort($this->table->imports);

        return implode("\n", array_map(function (string $import) {
            return "use $import;";
        }, array_unique($this->table->imports)));
    }

    public function properties(): string
    {
        return implode("\n", array_map(function (Property $property) {
            return ' * @property'.($property->readOnly ? '-read' : '').' '.$property->return.' '.$property->field;
        }, $this->table->properties));
    }

    public function table(): string
    {
        if (config('models-generator.table')) {
            return $this->spacer.'protected $table = \''.$this->table->name.'\';'."\n"."\n";
        }

        return '';
    }

    public function primaryKey(): string
    {
        if (config('models-generator.primary_key')) {
            return $this->spacer.'protected $primaryKey = \''.$this->table->primaryKey.'\';'."\n"."\n";
        }

        return '';
    }

    public function timestamps(): string
    {
        return $this->spacer.'public $timestamps = '.($this->table->timestamps ? 'true' : 'false').';'."\n"."\n";
    }

    public function casts(): string
    {
        $body = '';

        if (count($this->table->casts) > 0) {
            $body .= $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * @return array<string, string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected function casts(): array'."\n";
            $body .= $this->spacer.'{'."\n";
            $body .= str_repeat($this->spacer, 2).'return ['."\n";
            foreach ($this->table->casts as $column => $type) {
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
        if (count($this->table->fillable) > 0) {
            $body = $this->spacer.'/**'."\n";
            $body .= $this->spacer.' * The attributes that are mass assignable.'."\n";
            $body .= $this->spacer.' *'."\n";
            $body .= $this->spacer.' * @var list<string>'."\n";
            $body .= $this->spacer.' */'."\n";
            $body .= $this->spacer.'protected $fillable = ['."\n";
            foreach ($this->table->fillable as $fillable) {
                $body .= str_repeat($this->spacer, 2).'\''.$fillable.'\','."\n";
            }
            $body .= $this->spacer.'];'."\n"."\n";

            return $body;
        }

        return '';
    }

    public function hidden(): string
    {
        if (count($this->table->hidden) > 0) {
            $body = $this->spacer.'protected $hidden = ['."\n";
            foreach ($this->table->hidden as $hidden) {
                $body .= str_repeat($this->spacer, 2).'\''.$hidden.'\','."\n";
            }
            $body .= $this->spacer.'];'."\n"."\n";

            return $body;
        }

        return '';
    }

    public function parent(): string
    {
        $parent = 'Model';

        if (count((array) config('models-generator.interfaces', [])) > 0) {
            /** @var list<string> $interfaces */
            $interfaces = (array) config('models-generator.interfaces');
            asort($interfaces);

            $parent .= ' implements '.implode(', ', array_map(function (string $interface) {
                $parts = explode('\\', $interface);

                return end($parts);
            }, $interfaces));

            return $parent;
        }

        return $parent;
    }

    public function traits(): string
    {
        /** @var array<string> $traitsToUse */
        $traitsToUse = config('models-generator.traits', []);
        if ($this->table->softDeletes) {
            $traitsToUse[] = 'SoftDeletes';
        }
        if (count($traitsToUse) > 0) {
            asort($traitsToUse);

            $body = '';
            foreach ($traitsToUse as $trait) {
                $parts = explode('\\', $trait);
                $body .= $this->spacer.'use '.end($parts).';'."\n";
            }

            $body .= "\n";

            return $body;
        }

        return '';
    }

    public function body(): string
    {
        return $this->traits().$this->table().$this->primaryKey().$this->timestamps().$this->fillable().$this->hidden().$this->casts().$this->relationships();
    }

    private function hasMany(): string
    {
        $content = '';
        foreach ($this->table->hasMany as $hasMany) {
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

        return $content;
    }

    private function belongTo(): string
    {
        $content = '';
        foreach ($this->table->belongsTo as $belongsTo) {
            $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return BelongsTo<'.$belongsTo->foreignClassName.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$belongsTo->name.'(): BelongsTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsTo('.$belongsTo->foreignClassName.'::class, \''.$belongsTo->foreignColumnName.'\''.($belongsTo->localColumnName != $this->table->primaryKey ? ', \''.$belongsTo->localColumnName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }

    private function belongsToMany(): string
    {
        $content = '';

        foreach ($this->table->belongsToMany as $belongsToMany) {
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
        foreach ($this->table->morphTo as $morphTo) {
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
        foreach ($this->table->morphMany as $morphMany) {
            $content .= "\n"."\n";
            $content .= $this->spacer.'/**'."\n";
            $content .= $this->spacer.' * @return MorphMany<'.$morphMany->related.', $this>'."\n";
            $content .= $this->spacer.' */'."\n";
            $content .= $this->spacer.'public function '.$morphMany->name.'(): MorphMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphMany('.$morphMany->related.'::class, \''.$morphMany->name.'\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
