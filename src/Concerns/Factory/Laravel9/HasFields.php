<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Factory\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;

/**
 * @mixin Writer
 */
trait HasFields
{
    public function fields(): string
    {
        $body = '';

        if (count($this->entity->casts) > 0) {
            foreach ($this->entity->casts as $column => $type) {
                if (! is_null($this->entity->primaryKey) && $this->entity->primaryKey->name === $column && $this->entity->primaryKey->autoIncrement) {
                    continue;
                }

                if (! in_array($column, $this->entity->uuids)) {
                    $body .= str_repeat($this->spacer, 3).'\''.$column.'\' => '.$this->generateLaravelFakeCode($column, $type).','."\n";
                }
            }
        }

        if (count($this->entity->uuids) > 0) {
            foreach ($this->entity->uuids as $column) {
                if (! is_null($this->entity->primaryKey) && $this->entity->primaryKey->name === $column && $this->entity->primaryKey->autoIncrement) {
                    continue;
                }

                $body .= str_repeat($this->spacer, 3).'\''.$column.'\' => fake()->uuid,'."\n";
            }
        }

        return $body.str_repeat($this->spacer, 2);
    }

    private function generateLaravelFakeCode(string $columnName, string $columnType): string
    {
        if ($columnName === 'name') {
            return 'fake()->name';
        }

        if ($columnName === 'email') {
            return 'fake()->unique()->safeEmail()';
        }

        if ($columnName === 'address') {
            return 'fake()->address';
        }

        if ($columnName === 'title') {
            return 'fake()->title';
        }

        if ($columnName === 'password') {
            return '\Illuminate\Support\Str::password(24)';
        }

        if ($columnName === 'created_at' || $columnName === 'updated_at' || $columnName === 'deleted_at') {
            return 'now()';
        }

        return match ($columnType) {
            'integer' => 'fake()->numberBetween(1, '.rand(2, 10000).')',
            'float' => 'fake()->randomFloat(2, 1, '.rand(2, 10000).')',
            'boolean' => 'fake()->boolean',
            'string' => 'fake()->word',
            'datetime' => 'fake()->dateTime()',
            default => '',
        };
    }
}
