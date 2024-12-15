<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel11;

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
        return implode("\n", array_map(function (string $property) {
            return " * @property $property";
        }, $this->table->properties));
    }

    public function table(): string
    {
        if (config('models-generator.table')) {
            return $this->spacer.'public $table = \''.$this->table->name.'\';'."\n"."\n";
        }

        return '';
    }

    public function primaryKey(): string
    {
        if (config('models-generator.primary_key')) {
            return $this->spacer.'public $primaryKey = \''.$this->table->primaryKey.'\';'."\n"."\n";
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
                $body .= str_repeat($this->spacer, 3).'\''.$column.'\' => '.'\''.$type.'\','."\n";
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
            $body = $this->spacer.'protected $fillable = ['."\n";
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
        if (count(config('models-generator.traits', [])) > 0) {
            $body = '';
            foreach (config('models-generator.traits') as $trait) {
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
            if ($this->table->thereIsAnotherHasMany($hasMany)) {
                $relationName = Str::camel(Str::plural($hasMany->name)).'As'.ucfirst(Str::camel(str_replace($this->table->primaryKey, '', $hasMany->foreignKeyName)));
            } else {
                $relationName = Str::camel(Str::plural($hasMany->name));
            }
            $content .= "\n"."\n";
            $content .= $this->spacer.'public function '.$relationName.'(): HasMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->hasMany('.ucfirst(Str::camel($hasMany->related)).'::class, \''.$hasMany->foreignKeyName.'\''.(! empty($hasMany->localKeyName) ? ', \''.$hasMany->localKeyName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }

    private function belongTo(): string
    {
        $content = '';
        foreach ($this->table->belongsTo as $belongsTo) {
            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName())));
            $foreignColumnName = $belongsTo->foreignKey->getForeignColumns()[0];
            $localColumnName = $belongsTo->foreignKey->getLocalColumns()[0];
            if ($localColumnName != $this->table->primaryKey) {
                $relationName = Str::camel(str_replace($this->table->primaryKey, '', $localColumnName));
            } else {
                $relationName = Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName()));
            }
            $content .= "\n"."\n";
            $content .= $this->spacer.'public function '.$relationName.'(): BelongsTo'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsTo('.$foreignClassName.'::class, \''.$foreignColumnName.'\''.($localColumnName != $this->table->primaryKey ? ', \''.$localColumnName.'\'' : '').');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }

    private function belongsToMany(): string
    {
        $content = '';

        foreach ($this->table->belongsToMany as $belongsToMany) {
            if ($belongsToMany->pivot == $this->table->name.'_'.$belongsToMany->related ||
                $belongsToMany->pivot == $belongsToMany->related.'_'.$this->table->name) {
                $relationName = Str::camel(Str::plural($belongsToMany->related));
            } else {
                if (Str::start($belongsToMany->related, $belongsToMany->pivot)) {
                    $related = str_replace($belongsToMany->pivot.'_', '', $belongsToMany->related);
                } else {
                    $related = $belongsToMany->related;
                }
                $relationName = Str::camel(str_replace("{$this->table->name}_", '', $belongsToMany->pivot).'_'.Str::plural($related));
            }

            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsToMany->related)));
            //$foreignColumnName = $belongsTo->foreignKey->getForeignColumns()[0];
            $content .= "\n"."\n";
            $content .= $this->spacer.'public function '.$relationName.'(): BelongsToMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->belongsToMany('.$foreignClassName.'::class, \''.$belongsToMany->pivot.'\', \''.$belongsToMany->foreignPivotKey.'\', \''.$belongsToMany->relatedPivotKey.'\')'."\n";
            $content .= str_repeat($this->spacer, 3).(count($belongsToMany->pivotAttributes) > 0 ? '->withPivot(\''.implode('\', \'', $belongsToMany->pivotAttributes).'\')' : '')."\n";
            $content .= str_repeat($this->spacer, 3).($belongsToMany->timestamps ? '->withTimestamps()' : '').';'."\n";
            $content .= '}';
        }

        return $content;
    }

    private function morphTo(): string
    {
        $content = '';
        foreach ($this->table->morphTo as $morphTo) {
            $content .= "\n"."\n";
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
            $content .= $this->spacer.'public function '.$morphMany->name.'(): MorphMany'."\n";
            $content .= $this->spacer.'{'."\n";
            $content .= str_repeat($this->spacer, 2).'return $this->morphMany('.$morphMany->related.'::class, \''.$morphMany->name.'\');'."\n";
            $content .= $this->spacer.'}';
        }

        return $content;
    }
}
