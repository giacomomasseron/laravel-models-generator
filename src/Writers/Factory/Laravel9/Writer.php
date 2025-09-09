<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory\Laravel9;

use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Factory\Laravel9\HasFields;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory\WriterInterface;

class Writer extends \GiacomoMasseroni\LaravelModelsGenerator\Writers\Factory\Writer implements WriterInterface
{
    use HasFields;
}
