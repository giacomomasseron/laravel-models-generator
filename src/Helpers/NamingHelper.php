<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Helpers;

use GiacomoMasseroni\LaravelModelsGenerator\Enums\RelationshipsNameCaseTypeEnum;
use Illuminate\Support\Str;

class NamingHelper
{
    public static function caseRelationName(string $name): string
    {
        return match (config('models-generator.relationships_name_case_type')) {
            RelationshipsNameCaseTypeEnum::SNAKE_CASE => Str::snake($name),
            default => Str::camel($name),
        };
    }
}
