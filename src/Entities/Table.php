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

    /**
     * @var array<MorphMany>
     */
    public array $morphMany = [];

    /**
     * @var array<MorphTo>
     */
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
