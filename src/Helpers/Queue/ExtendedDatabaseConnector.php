<?php
namespace Common\Helpers\Queue;

use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Support\Arr;

class ExtendedDatabaseConnector extends DatabaseConnector
{
    public function connect(array $config)
    {
        return new ExtendedDatabaseQueue(
            $this->connections->connection(Arr::get($config, 'connection')),
            $config['table'],
            $config['queue'],
            Arr::get($config, 'expire', 60)
        );
    }
}
