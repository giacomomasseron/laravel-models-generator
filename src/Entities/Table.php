<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphTo;

class Table
{
    /** @var array<string> */
    public array $imports = [];

    /** @var array<string> */
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

    public string $primaryKey = 'id';

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

        if ($alreadyInserted !== false) {
            $this->belongsToMany[] = $belongsToMany;
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

        if ($alreadyInserted !== false) {
            $this->belongsTo[] = $belongsTo;
        }

        return $this;
    }

    public function thereIsAnotherHasMany(HasMany $hasMany): bool
    {
        foreach ($this->hasMany as $rel) {
            if ($rel !== $hasMany && $rel->name === $hasMany->name) {
                return true;
            }
        }

        return false;
    }
}
