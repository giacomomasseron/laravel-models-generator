<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Entities;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsTo;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\BelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\HasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Relationships\MorphTo;
use GiacomoMasseroni\LaravelModelsGenerator\Factories\DBALVersionFactory;

class Entity
{
    public DBALInterface $dbalVersion;

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

    public bool $abstract = false;

    public ?string $parent = null;

    /** @var array<string> */
    public array $interfaces = [];

    /** @var array<string> */
    public array $traits = [];

    /** @var array<string> */
    public array $uuids = [];

    /** @var array<string> */
    public array $ulids = [];

    /** @var array<string> */
    public array $globalScopes = [];

    public bool $timestamps = false;

    public ?bool $showTableProperty = null;

    public ?bool $showConnectionProperty = null;

    public bool $showTimestampsProperty = true;

    public bool $softDeletes = false;

    public ?string $namespace = null;

    public ?PrimaryKey $primaryKey = null;

    public ?string $connection = null;

    public ?string $observer = null;

    public ?string $queryBuilder = null;

    public function __construct(public string $name, public string $className)
    {
        $this->dbalVersion = DBALVersionFactory::create();

        /** @var array<string> $parts */
        $parts = explode('\\', (string) config('models-generator.parent', 'Model'));
        $this->parent = $parts ? end($parts) : 'Model';
        $this->interfaces = (array) config('models-generator.interfaces', []);
        $this->traits = (array) config('models-generator.traits', []);
        $this->showTableProperty = (bool) config('models-generator.table', false);
        $this->showConnectionProperty = (bool) config('models-generator.connection', false);
        // $this->className = (string) implode(array_map('ucfirst', explode('.' ,$this->className)));
    }

    public function importLaravelModel(): bool
    {
        return ! str_contains($this->parent ?? '', 'Base');
    }

    public function cleanForBase(): void
    {
        $this->hasMany = [];
        $this->belongsTo = [];
        $this->belongsToMany = [];
        $this->morphMany = [];
        $this->morphTo = [];
        $this->casts = [];
        $this->fillable = [];
        $this->traits = [];
        $this->uuids = [];
        $this->properties = [];
        $this->interfaces = [];
        $this->primaryKey = null;
        $this->showTableProperty = false;
        $this->showTimestampsProperty = false;
        $this->parent = 'Base'.$this->className;
        $this->abstract = false;
        $this->observer = null;
        $this->queryBuilder = null;
        $this->globalScopes = [];
        $this->namespace = (string) config('models-generator.namespace', 'App\Models');
        $this->imports = [$this->namespace.'\\Base\\'.$this->className.' as Base'.$this->className];
    }
}
