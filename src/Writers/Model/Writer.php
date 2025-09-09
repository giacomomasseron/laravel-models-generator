<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Model;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;

abstract class Writer implements WriterInterface
{
    public string $spacer = '    ';

    public function __construct(
        public string $className,
        public Entity $entity,
        public string $stubContent,
        public string $stubEmptyContent,
        protected bool $isBase = false
    ) {}

    public function writeModelFile(): string
    {
        $search = [
            '{{strict}}',
            '{{namespace}}',
            '{{properties}}',
            '{{queryBuilder}}',
            '{{globalScopesAsAttribute}}',
            '{{observer}}',
            '{{abstract}}',
            '{{className}}',
            '{{imports}}',
            '{{parent}}',
            '{{body}}',
        ];
        $replace = [
            $this->strict(),
            $this->namespace(),
            $this->properties(),
            $this->queryBuilder(),
            $this->globalScopesAsAttribute(),
            $this->observer(),
            $this->abstract(),
            $this->className,
            $this->imports(),
            $this->parent(),
            $this->body(),
        ];

        if (empty($this->body())) {
            return str_replace($search, $replace, $this->stubEmptyContent);
        }

        return str_replace($search, $replace, $this->stubContent);
    }

    abstract public function traits(): string;

    abstract public function observer(): string;

    abstract public function queryBuilder(): string;

    abstract public function globalScopesAsAttribute(): string;

    abstract public function abstract(): string;

    abstract public function connection(): string;

    abstract public function primaryKey(): string;

    abstract public function fillable(): string;

    abstract public function defaultValues(): string;

    abstract public function hidden(): string;

    abstract public function imports(): string;

    abstract public function properties(): string;

    abstract public function casts(): string;

    abstract public function uuids(): string;

    abstract public function booted(): string;

    // abstract public function relationships(): string;

    // abstract public function body(): string;

    abstract protected function hasMany(): string;

    abstract protected function belongTo(): string;

    abstract protected function belongsToMany(): string;

    abstract protected function morphTo(): string;

    abstract protected function morphMany(): string;

    abstract public function parent(): string;

    public function namespace(): string
    {
        return $this->entity->namespace ?? (string) config('models-generator.namespace', 'App\Models');
    }

    public function strict(): string
    {
        return config('models-generator.strict_types', true) ? "\n".'declare(strict_types=1);'."\n" : '';
    }

    public function body(): string
    {
        return implode("\n\n", array_filter([
            $this->traits(),
            $this->table(),
            $this->connection(),
            $this->primaryKey(),
            $this->timestamps(),
            $this->fillable(),
            $this->defaultValues(),
            $this->hidden(),
            $this->casts(),
            $this->uuids(),
            $this->booted(),
            $this->relationships(),
        ]));
    }

    public function table(): string
    {
        if ($this->entity->showTableProperty) {
            return $this->spacer.'protected $table = \''.$this->entity->name.'\';';
        }

        return '';
    }

    public function timestamps(): string
    {
        $content = '';

        if ($this->entity->showTimestampsProperty && $this->entity->timestamps) {
            $timestampsFields = config('models-generator.timestamps.fields', []);
            if (! empty($timestampsFields['created_at'])) {
                $content .= $this->spacer.'public const CREATED_AT = \''.$timestampsFields['created_at'].'\';'."\n";
            }
            if (! empty($timestampsFields['updated_at'])) {
                $content .= $this->spacer.'public const UPDATED_AT = \''.$timestampsFields['updated_at'].'\';'."\n";
            }

            if (! empty(config('models-generator.timestamps.format', null))) {
                $content .= $this->spacer.'protected $dateFormat = \''.config('models-generator.timestamps.format').'\';'."\n";
            }
        }

        return $this->entity->showTimestampsProperty ? (! empty($content) ? $content."\n" : '').$this->spacer.'public $timestamps = '.($this->entity->timestamps ? 'true' : 'false').';' : '';
    }

    public function relationships(): string
    {
        return implode("\n\n", array_filter([
            $this->hasMany(),
            $this->belongTo(),
            $this->belongsToMany(),
            $this->morphTo(),
            $this->morphMany(),
        ]));
    }
}
