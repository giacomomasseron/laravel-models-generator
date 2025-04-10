<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers;

use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;

abstract class Writer implements WriterInterface
{
    public string $spacer = '    ';

    public bool $prevElementWasNotEmpty = false;

    public function __construct(public string $className, public Entity $entity, public string $stubContent, protected bool $isBase = false) {}

    public function writeModelFile(): string
    {
        $search = [
            '{{strict}}',
            '{{namespace}}',
            '{{properties}}',
            '{{rules}}',
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
            $this->rules(),
            $this->abstract(),
            $this->className,
            $this->imports(),
            $this->parent(),
            $this->body(),
        ];

        return str_replace($search, $replace, $this->stubContent);
    }

    abstract public function traits(): string;

    abstract public function abstract(): string;

    abstract public function primaryKey(): string;

    abstract public function fillable(): string;

    abstract public function hidden(): string;

    abstract public function imports(): string;

    abstract public function properties(): string;

    abstract public function rules(): string;

    abstract public function casts(): string;

    abstract public function relationships(): string;

    // abstract public function body(): string;

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
        return $this->traits().$this->table().$this->primaryKey().$this->timestamps().$this->fillable().$this->rules().$this->hidden().$this->casts().$this->relationships();
    }

    public function table(): string
    {
        if ($this->entity->showTableProperty) {
            $content = '';
            if ($this->prevElementWasNotEmpty) {
                $content = "\n";
            }

            $this->prevElementWasNotEmpty = true;

            return $content."\n".$this->spacer.'protected $table = \''.$this->entity->name.'\';'."\n";
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function timestamps(): string
    {
        $content = '';
        if ($this->prevElementWasNotEmpty) {
            $content = "\n";
        }

        $this->prevElementWasNotEmpty = true;

        if ($this->entity->showTimestampsProperty && $this->entity->timestamps) {
            $timestampsFields = config('models-generator.timestamps.fields', []);
            if (! empty($timestampsFields['created_at'])) {
                $content .= "\n".$this->spacer.'public const CREATED_AT = \''.$timestampsFields['created_at'].'\';'."\n";
            }
            if (! empty($timestampsFields['updated_at'])) {
                $content .= "\n".$this->spacer.'public const UPDATED_AT = \''.$timestampsFields['updated_at'].'\';'."\n";
            }

            if (! empty(config('models-generator.timestamps.format', null))) {
                $content .= "\n".$this->spacer.'protected $dateFormat = \''.config('models-generator.timestamps.format').'\';'."\n";
            }
        }

        return $this->entity->showTimestampsProperty ? $content."\n".$this->spacer.'public $timestamps = '.($this->entity->timestamps ? 'true' : 'false').';' : '';
    }
}
