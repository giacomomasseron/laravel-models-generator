<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers;

use Doctrine\DBAL\Exception;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Exceptions\DatabaseDriverNotFound;

class DriverFacade
{
    /**
     * @throws DatabaseDriverNotFound|Exception
     */
    public static function instance(string $driver, ?string $connection = null, ?string $schema = null, ?string $table = null): DriverConnectorInterface
    {
        return match ($driver) {
            'mysql' => new MySQL\Connector($connection, $schema, $table),
            'sqlite' => new SQLite\Connector($connection, $schema, $table),
            'pgsql' => new PostgreSQL\Connector($connection, $schema, $table),
            'sqlsrv' => new SQLServer\Connector($connection, $schema, $table),
            default => throw new DatabaseDriverNotFound($driver),
        };
    }
}
