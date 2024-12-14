<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers\MySQL;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverConnector;

class Connector extends DriverConnector implements DriverConnectorInterface
{
    public function connectionParams(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            'dbname' => $this->schema,
            'user' => (string) config('database.connections.'.config('database.default').'.username'),
            'password' => (string) config('database.connections.'.config('database.default').'.password'),
            'host' => (string) config('database.connections.'.config('database.default').'.host'),
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
        ];
    }
}
