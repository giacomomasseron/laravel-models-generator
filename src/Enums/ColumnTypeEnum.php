<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Enums;

enum ColumnTypeEnum: string
{
    case SMALLINT = 'smallint';
    case INT = 'int';
    case BIGINT = 'bigint';
    case DOUBLE = 'double';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOLEAN = 'boolean';
    case DATETIME = 'datetime';
}
