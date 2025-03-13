<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallFloatType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Entity;
use Illuminate\Support\Str;

if (! function_exists('dbEntityNameToModelName')) {
    function dbEntityNameToModelName(string $dbEntityName): string
    {
        return ucfirst(Str::camel(Str::singular(str_replace(config('models-generator.table_prefix', ''), '', $dbEntityName))));
    }
}

if (! function_exists('laravelColumnTypeForCast')) {
    function laravelColumnTypeForCast(Type $type, ?Entity $dbTable = null): ?string
    {
        if ($type instanceof SmallIntType ||
            $type instanceof BigIntType ||
            $type instanceof IntegerType
        ) {
            return 'integer';
        }
        if ($type instanceof DateType ||
            $type instanceof DateTimeType ||
            $type instanceof DateImmutableType ||
            $type instanceof DateTimeImmutableType ||
            $type instanceof DateTimeTzType ||
            $type instanceof DateTimeTzImmutableType
        ) {
            if ($dbTable !== null) {
                $dbTable->imports[] = 'Carbon\Carbon';
            }

            return 'datetime';
        }
        if ($type instanceof StringType ||
            $type instanceof TextType) {
            return 'string';
        }
        if ($type instanceof DecimalType ||
            $type instanceof SmallFloatType ||
            $type instanceof FloatType
        ) {
            return 'float';
        }
        if ($type instanceof BooleanType) {
            return 'bool';
        }

        return null;
    }
}
