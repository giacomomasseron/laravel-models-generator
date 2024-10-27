<?php

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers\MySQL;

use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverConnector;

class Connector extends DriverConnector implements DriverConnectorInterface
{
    public function connectionParams(): array
    {
        return [
            'dbname' => $this->schema,
            'user' => config('database.connections.'.config('database.default').'.username'),
            'password' => config('database.connections.'.config('database.default').'.password'),
            'host' => config('database.connections.'.config('database.default').'.host'),
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
        ];
    }
}
