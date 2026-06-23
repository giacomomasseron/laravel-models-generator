<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers\MySQL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
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
            'dbname' => $this->schema,
            'user' => (string) config('database.connections.'.config('database.default').'.username'),
            'password' => (string) config('database.connections.'.config('database.default').'.password'),
            'host' => (string) config('database.connections.'.config('database.default').'.host'),
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
        ];
    }

    /**
     * Introspects the columns of a view.
     *
     * DBAL 4.4's schema introspection filters on TABLE_TYPE = 'BASE TABLE', so it never returns
     * columns for views. They are therefore read here directly from information_schema.
     *
     * @return array<string, Column>
     *
     * @throws Exception
     */
    private function getViewColumns(string $viewName): array
    {
        $platform = $this->conn->getDatabasePlatform();

        $rows = $this->conn->executeQuery(
            'SELECT COLUMN_NAME AS name, DATA_TYPE AS type, IS_NULLABLE AS nullable '.
            'FROM information_schema.COLUMNS '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? '.
            'ORDER BY ORDINAL_POSITION',
            [$this->schema, $viewName]
        )->fetchAllAssociative();

        $columns = [];
        foreach ($rows as $row) {
            $name = (string) $row['name'];
            $dbType = strtolower((string) $row['type']);

            $doctrineType = $platform->hasDoctrineTypeMappingFor($dbType)
                ? $platform->getDoctrineTypeMapping($dbType)
                : Types::STRING;

            $columns[strtolower($name)] = new Column($name, Type::getType($doctrineType), [
                'notnull' => $row['nullable'] === 'NO',
            ]);
        }

        return $columns;
    }

    private function getView(string $viewName): View
    {
        $columns = $this->getViewColumns($viewName);
        $properties = [];

        $dbView = new View($viewName, dbEntityNameToModelName($viewName));
        $dbView->fillable = array_diff(
            array_keys($columns),
            ['created_at', 'updated_at', 'deleted_at']
        );

        /** @var Column $column */
        foreach ($columns as $column) {
            $columnName = $this->dbal()->getColumnName($column);
            $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbView);
            $dbView->casts[$columnName] = $this->laravelColumnTypeForCast($this->mapColumnType($column->getType()), $dbView);

            $properties[] = new Property(
                '$'.$columnName,
                ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType).($column->getNotnull() ? '' : '|null'),
                true
            );
        }
        $dbView->properties = $properties;

        if (resolveLaravelVersion()->check(13)) {
            $dbView->imports[] = 'Illuminate\Database\Eloquent\Attributes\Table';

            if (count($dbView->fillable) > 0) {
                $dbView->imports[] = 'Illuminate\Database\Eloquent\Attributes\Fillable';
            }

            if (count($dbView->hidden) > 0) {
                $dbView->imports[] = 'Illuminate\Database\Eloquent\Attributes\Hidden';
            }

            if ($dbView->showConnectionProperty && ! empty($dbView->connection)) {
                $dbView->imports[] = 'Illuminate\Database\Eloquent\Attributes\Connection';
            }
        }

        return $dbView;
    }

    public function listViews(): array
    {
        /** @var array<string, View> $dbViews */
        $dbViews = [];

        $sql = "SHOW FULL TABLES IN $this->schema WHERE TABLE_TYPE LIKE 'VIEW'";
        $rows = DB::select($sql);
        // dd($rows);

        foreach ($rows as $row) {
            $columnName = "Tables_in_{$this->schema}";
            $dbViews[$row->$columnName] = $this->getView($row->$columnName);
        }

        return $dbViews;
    }
}
