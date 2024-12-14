<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers\SQLite;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverConnector;

class Connector extends DriverConnector implements DriverConnectorInterface
{
    public function connectionParams(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
            'path' => (string) config('database.connections.'.config('database.default').'.database'),
        ];
    }
}
