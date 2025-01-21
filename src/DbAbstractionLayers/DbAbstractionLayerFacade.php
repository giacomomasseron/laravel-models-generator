<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DbAbstractionLayerInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Exceptions\DbAbstractionLayerNotFound;

class DbAbstractionLayerFacade
{
    /**
     * @throws DbAbstractionLayerNotFound
     */
    public static function instance(): DbAbstractionLayerInterface
    {
        throw new DbAbstractionLayerNotFound;
    }
}
