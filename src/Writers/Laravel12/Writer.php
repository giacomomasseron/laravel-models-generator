<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Writers\Laravel12;

use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11\HasBelongsToMany;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11\HasBelongTo;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11\HasCasts;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11\HasHasMany;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel11\HasMorphTo;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasAbstract;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasDefaultValues;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasFillables;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasHidden;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasImports;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasMorphMany;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasParent;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasPrimaryKey;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasProperties;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasTraits;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\Laravel9\HasUuids;
use GiacomoMasseroni\LaravelModelsGenerator\Writers\WriterInterface;

class Writer extends \GiacomoMasseroni\LaravelModelsGenerator\Writers\Writer implements WriterInterface
{
    use HasAbstract;
    use HasBelongsToMany;
    use HasBelongTo;
    use HasCasts;
    use HasDefaultValues;
    use HasFillables;
    use HasHasMany;
    use HasHidden;
    use HasImports;
    use HasMorphMany;
    use HasMorphTo;
    use HasParent;
    use HasPrimaryKey;
    use HasProperties;
    use HasTraits;
    use HasUuids;
}
