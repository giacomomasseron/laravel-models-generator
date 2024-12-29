<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphTo;
use Illuminate\Support\Str;

class Table
{
    /** @var array<string> */
    public array $imports = [];

    /** @var array<Property> */
    public array $properties = [];

    /** @var array<HasMany> */
    public array $hasMany = [];

    /** @var array<BelongsTo> */
    public array $belongsTo = [];

    /** @var array<BelongsToMany> */
    public array $belongsToMany = [];

    /** @var array<MorphMany> */
    public array $morphMany = [];

    /** @var array<MorphTo> */
    public array $morphTo = [];

    /** @var array<string> */
    public array $hidden = [];

    /** @var array<string> */
    public array $fillable = [];

    /** @var array<string> */
    public array $casts = [];

    public bool $timestamps = false;

    public bool $softDeletes = false;

    public PrimaryKey $primaryKey;

    public function __construct(public string $name, public string $className) {}

    public function addHasMany(HasMany $hasMany): self
    {
        $this->hasMany[] = $hasMany;

        return $this;
    }

    public function addBelongsToMany(BelongsToMany $belongsToMany): self
    {
        $alreadyInserted = false;
        foreach ($this->belongsToMany as $rel) {
            if ($rel->related === $belongsToMany->related) {
                $alreadyInserted = true;
            }
        }

        if ($alreadyInserted === false) {
            if ($belongsToMany->pivot == $this->name.'_'.$belongsToMany->related ||
                $belongsToMany->pivot == $belongsToMany->related.'_'.$this->name) {
                $relationName = Str::camel(Str::plural($belongsToMany->related));
            } else {
                if (Str::start($belongsToMany->related, $belongsToMany->pivot)) {
                    $related = str_replace($belongsToMany->pivot.'_', '', $belongsToMany->related);
                } else {
                    $related = $belongsToMany->related;
                }
                $relationName = Str::camel(str_replace("{$this->name}_", '', $belongsToMany->pivot).'_'.Str::plural($related));
            }
            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsToMany->related)));
            $belongsToMany->name = $relationName;
            $belongsToMany->foreignClassName = $foreignClassName;

            $this->belongsToMany[] = $belongsToMany;

            $this->properties[] = new Property('$'.$belongsToMany->name, 'Collection|'.$belongsToMany->foreignClassName.'[]', false);
            $this->imports[] = 'Illuminate\Database\Eloquent\Collection';
        }

        return $this;
    }

    public function addBelongsTo(BelongsTo $belongsTo): self
    {
        $alreadyInserted = false;
        foreach ($this->belongsTo as $rel) {
            if ($rel->foreignKey === $belongsTo->foreignKey) {
                $alreadyInserted = true;
            }
        }

        if ($alreadyInserted === false) {
            $foreignClassName = ucfirst(Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName())));
            $foreignColumnName = $belongsTo->foreignKey->getForeignColumns()[0];
            $localColumnName = $belongsTo->foreignKey->getLocalColumns()[0];
            if (str_contains($localColumnName, $foreignColumnName) && $localColumnName != $foreignColumnName) {
                $relationName = Str::camel(str_replace($foreignColumnName, '', $localColumnName));
            } else {
                $relationName = Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName()));
            }
            $belongsTo->name = $relationName;
            $belongsTo->foreignClassName = $foreignClassName;
            $belongsTo->foreignColumnName = $foreignColumnName;
            $belongsTo->localColumnName = $localColumnName;

            $this->belongsTo[$belongsTo->foreignKey->getName()] = $belongsTo;

            $this->properties[] = new Property('$'.$belongsTo->name, $belongsTo->foreignClassName, false);
        }

        return $this;
    }

    public function thereIsAnotherHasMany(HasMany $hasMany): bool
    {
        foreach ($this->hasMany as $rel) {
            if ($rel !== $hasMany && $rel->related === $hasMany->related) {
                return true;
            }
        }

        return false;
    }

    public function fixRelationshipsName(): void
    {
        foreach ($this->hasMany as $key => $hasMany) {
            if ($this->thereIsAnotherHasMany($hasMany)) {
                $this->hasMany[$key]->name = Str::camel(Str::plural($hasMany->name)).'As'.ucfirst(Str::camel(str_replace($this->primaryKey->name, '', $hasMany->foreignKeyName)));
            } else {
                $this->hasMany[$key]->name = Str::camel(Str::plural($hasMany->name));
            }
            $this->properties[] = new Property('$'.$hasMany->name, 'Collection|'.$hasMany->related.'[]', false);
            $this->imports[] = 'Illuminate\Database\Eloquent\Collection';
        }
    }
}
