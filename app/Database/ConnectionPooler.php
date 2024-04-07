<?php

namespace App\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Database\Connectors\SQLiteConnector;
use Illuminate\Database\Connectors\SqlServerConnector;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\ConnectionResolverInterface;

class ConnectionPooler implements ConnectionResolverInterface
{
    protected $factory;
    protected $connections;

    public function __construct(ConnectionFactory $factory)
    {
        $this->factory = $factory;
        $this->connections = [];
    }

    public function connection($name = null)
    {
        $name = $name ?: 'default';

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    protected function makeConnection($name)
    {
        $config = $this->getConfig($name);

        $driver = $config['driver'];

        $connector = $this->createConnector($driver);

        $connection = $this->factory->make($config, $driver);

        return $this->prepare($connection);
    }

    protected function createConnector($driver)
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlConnector;
            case 'pgsql':
                return new PostgresConnector;
            case 'sqlite':
                return new SQLiteConnector;
            case 'sqlsrv':
                return new SqlServerConnector;
        }
    }

    protected function getConfig($name)
    {
        $connections = config('database.connections');

        if (!isset($connections[$name])) {
            throw new InvalidArgumentException("Database [$name] not configured.");
        }

        return $connections[$name];
    }

    protected function prepare(Connection $connection)
    {
        return $connection->setReconnector(function ($connection) {
            $this->reconnect($connection);
        });
    }

    protected function reconnect(Connection $connection)
    {
        $this->disconnect($connection);

        $connection->setPdo(null)->setReadPdo(null);
    }

    protected function disconnect(Connection $connection)
    {
        $connection->disconnect();
    }
    public function getDefaultConnection()
    {
        return 'default';
    }
    public function setDefaultConnection($name)
    {
        return;
    }
}
