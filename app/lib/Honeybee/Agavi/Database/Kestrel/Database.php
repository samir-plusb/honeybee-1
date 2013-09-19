<?php

namespace Honeybee\Agavi\Database\Kestrel;

use Honeybee\Agavi\Database\IDatabaseSetup;

class Database extends \AgaviDatabase
{
    protected $connection;

    protected function connect()
    {
        $host = $this->getParameter('host', Client::DEFAULT_HOST);
        $port = $this->getParameter('port', Client::DEFAULT_PORT);

        try {
            $this->connection = new Client();
            $this->connection->connect($this->getParameter('servers'));
        } catch (Exception $e) {
            throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function shutdown()
    {
        $this->connection = null;
    }
}
