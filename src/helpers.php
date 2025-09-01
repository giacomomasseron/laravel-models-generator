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

if (! function_exists('isRelationshipToBeAdded')) {
    function isRelationshipToBeAdded(string $tableOfStartingRelationship, string $tableOfRelationship): bool
    {
        /** @var array<string, array<string>> $excludeRelationships */
        $excludeRelationships = config('models-generator.exclude_relationships', []);

        return ! isset($excludeRelationships[$tableOfStartingRelationship]) || (in_array($tableOfRelationship, $excludeRelationships[$tableOfStartingRelationship]) === false);
    }
}

if (! function_exists('deleteAllInFolder')) {
    /**
     * @param  string  $dir  Percorso della directory da svuotare
     * @param  string|null  $exclude  Nome della sottocartella da escludere (opzionale)
     */
    function deleteAllInFolder(string $dir, ?string $exclude = null): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if ($exclude !== null && $item === $exclude) {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path)) {
                deleteAllInFolder($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}
