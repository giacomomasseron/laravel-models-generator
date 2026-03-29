<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Model\Laravel13;

trait HasAttributes
{
    public function attributes(): string
    {
        $table = [];
        $table[] = '#[Table(';
        $table[] = $this->spacer."name: '{$this->entity->name}',";
        if (! is_null($this->entity->primaryKey)) {
            $table[] = $this->spacer."key: '{$this->entity->primaryKey->name}',";

            if (! $this->entity->primaryKey->autoIncrement) {
                $table[] = $this->spacer."keyType: 'string',";
                $table[] = $this->spacer.'incrementing: false,';
            }
        }

        if ($this->entity->showTimestampsProperty) {
            if ($this->entity->timestamps) {
                $table[] = $this->spacer.'timestamps: true,';
            } else {
                $table[] = $this->spacer.'timestamps: false,';
            }
        }

        $table[] = ')]';

        $attributes[] = implode("\n", $table);

        if ($this->entity->showConnectionProperty && ! empty($this->entity->connection)) {
            $attributes[] = "#[Connection('".$this->entity->connection."')]";
        }

        if (count($this->entity->fillable)) {
            $attributes[] = "#[Fillable(['".implode("', '", $this->entity->fillable)."'])]";
        }

        if (count($this->entity->hidden)) {
            $attributes[] = "#[Hidden(['".implode("','", $this->entity->hidden)."'])]";
        }

        return implode("\n", $attributes)."\n";
    }
}
