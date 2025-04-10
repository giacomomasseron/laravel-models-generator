<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers\SQLServer;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use GiacomoMasseroni\LaravelModelsGenerator\Concerns\DBALable;
use GiacomoMasseroni\LaravelModelsGenerator\Contracts\DriverConnectorInterface;
use GiacomoMasseroni\LaravelModelsGenerator\Drivers\DriverConnector;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\Property;
use GiacomoMasseroni\LaravelModelsGenerator\Entities\View;
use Illuminate\Support\Facades\DB;

class Connector extends DriverConnector implements DriverConnectorInterface
{
    use DBALable;

    /**
     * @throws Exception
     */
    public function __construct(?string $connection = null, ?string $schema = null, ?string $table = null)
    {
        parent::__construct($connection, $schema, $table);

        $this->conn = DriverManager::getConnection($this->connectionParams());
        $platform = $this->conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $this->sm = $this->conn->createSchemaManager();
    }

    public function connectionParams(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            'dbname'   => $this->schema,
            'user'     => (string) config('database.connections.' . config('database.default') . '.username'),
            'password' => (string) config('database.connections.' . config('database.default') . '.password'),
            'host'     => (string) config('database.connections.' . config('database.default') . '.host'),
            'driver'   => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
            'port'     => config('database.connections.' . config('database.default') . '.port') ?? 1433,
            'driverOptions' =>[
                'TrustServerCertificate' => true,
                'Encrypt' => true,
            ],
        ];
    }

    private function getView(string $viewName): View
    {
        $columns = $this->getEntityColumns($viewName);
        $properties = [];

        $dbView = new View($viewName, dbEntityNameToModelName($viewName));
        $dbView->fillable = array_diff(
            array_keys($columns),
            ['created_at', 'updated_at', 'deleted_at']
        );

        /** @var Column $column */
        foreach ($columns as $column) {
            $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbView);
            $dbView->casts[$column->getName()] = $this->laravelColumnTypeForCast($this->mapColumnType($column->getType()), $dbView);

            $properties[] = new Property(
                '$' . $column->getName(),
                ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType) . ($column->getNotnull() ? '' : '|null'),
                true
            );
        }
        $dbView->properties = $properties;

        return $dbView;
    }

    public function listViews(): array
    {
        /** @var array<string, View> $dbViews */
        $dbViews = [];
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS";
        $rows = DB::select($sql);

        foreach ($rows as $row) {
            $viewName = $row->TABLE_NAME;
            $dbViews[$viewName] = $this->getView($viewName);
        }

        return $dbViews;
    }
}
