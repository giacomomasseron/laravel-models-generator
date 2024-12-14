<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Contracts;

interface DriverConnectorInterface
{
    /**
     * @return array{
     *     'driver': 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv'
     * }
     */    public function connectionParams(): array;
}
