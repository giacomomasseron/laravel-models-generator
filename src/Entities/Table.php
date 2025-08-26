<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Helpers\NamingHelper;
use Illuminate\Support\Str;

class Table extends Entity
{
    public function addHasMany(HasMany $hasMany): self
    {
        $this->hasMany[] = $hasMany;

        return $this;
    }

    public function addBelongsToMany(BelongsToMany $belongsToMany): self
    {
        $alreadyInserted = false;
        foreach ($this->belongsToMany as $rel) {
            if ($rel->pivot === $belongsToMany->pivot && $rel->related === $belongsToMany->related) {
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
            $belongsToMany->name = NamingHelper::caseRelationName($relationName);
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
            $foreignClassName = ucfirst(Str::camel(Str::singular($this->dbalVersion->getForeignTableName($belongsTo->foreignKey))));
            // $foreignClassName = implode(array_map('ucfirst', explode('.' , ucfirst(Str::camel(Str::singular($belongsTo->foreignKey->getForeignTableName()))))));
            $foreignColumnName = $this->dbalVersion->getForeignColumns($belongsTo->foreignKey)[0];
            $localColumnName = $this->dbalVersion->getLocalColumns($belongsTo->foreignKey)[0];
            if (str_contains($localColumnName, $foreignColumnName) && $localColumnName != $foreignColumnName) {
                $relationName = Str::camel(str_replace($foreignColumnName, '', $localColumnName));
            } else {
                $relationName = Str::camel(Str::singular($this->dbalVersion->getForeignTableName($belongsTo->foreignKey)));
            }
            $belongsTo->name = NamingHelper::caseRelationName($relationName);
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
                $this->hasMany[$key]->name = NamingHelper::caseRelationName(Str::camel(Str::plural($hasMany->name)).'As'.ucfirst(Str::camel(str_replace($this->primaryKey->name ?? '', '', $hasMany->foreignKeyName))));
            } else {
                $this->hasMany[$key]->name = NamingHelper::caseRelationName(Str::plural($hasMany->name));
            }
            $this->properties[] = new Property('$'.$hasMany->name, 'Collection|'.$hasMany->related.'[]', false);
            $this->imports[] = 'Illuminate\Database\Eloquent\Collection';
        }
    }

    public function importLaravelModel(): bool
    {
        return ! str_contains($this->parent ?? '', 'Base');
    }
}
