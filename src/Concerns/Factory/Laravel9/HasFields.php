<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Concerns\Factory\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Writers\Model\Writer;
use Illuminate\Support\Str;
use Random\RandomException;

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

    /**
     * @throws RandomException
     */
    private function generateLaravelFakeCode(string $columnName, string $columnType): string
    {
        $sanitizedColumnName = ucfirst(Str::camel($columnName));
        $sanitizedColumnType = ucfirst(Str::camel($columnType));

        $classesPrefix = '\\GiacomoMasseroni\\LaravelModelsGenerator\\Generators\\Laravel9\\';

        $fullFakeClassName = $classesPrefix.'Fake'.$sanitizedColumnName;

        if (class_exists($fullFakeClassName)) {
            return (new $fullFakeClassName)->__toString(); /** @phpstan-ignore-line  */
        }

        $fullFakeClassName = $classesPrefix.'Fake'.$sanitizedColumnType;

        if (class_exists($fullFakeClassName)) {
            return (new $fullFakeClassName)->__toString(); /** @phpstan-ignore-line  */
        }

        return '';
    }
}
