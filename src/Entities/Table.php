<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;

class Table
{
    public array $hasOne = [];

    /**
     * @var array<HasMany>
     */
    public array $hasMany = [];

    /**
     * @var array<BelongsTo>
     */
    public array $belongsTo = [];

    /**
     * @var array<BelongsToMany>
     */
    public array $belongsToMany = [];

    public array $morphMany = [];

    public array $morphTo = [];

    /**
     * @var array<string>
     */
    public array $hidden = [];

    /**
     * @var array<string>
     */
    public array $fillable = [];

    /**
     * @var array<string>
     */
    public array $casts = [];

    public bool $timestamps = false;

    public string $primaryKey = 'id';

    public function __construct(public string $name, public string $className) {}
}
