<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Factories;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DBALInterface;
use GiacomoMasseroni\LaravelModelsGenerator\DbAbstractionLayers\DBAL;

class DBALVersionFactory
{
    public static function create(): DBALInterface
    {
        return match (InstalledVersions::satisfies(new VersionParser, 'doctrine/dbal', '4.*.*')) {
            true => new DBAL\DBAL4\DBAL,
            false => new DBAL\DBAL3\DBAL,
        };
    }
}
