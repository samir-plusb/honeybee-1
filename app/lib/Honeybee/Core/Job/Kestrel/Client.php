<?php

namespace Honeybee\Core\Job\Kestrel;

class Client
{
    const CMD_ABORT = '/abort';

    const CMD_CLOSE = '/close';

    const CMD_READ = '/open';

    const CMD_RELIABLE_READ = '/close/open';

    const CMD_PEEK = '/peek';

    protected $connected = false;

    protected $servers = array();

    protected $kestrel_api = null;

    public function __construct()
    {
        $this->kestrel_api = new \memcache();
    }

    public function addServer($host, $port)
    {
        $this->servers[] = array('host' => $host, 'port' => $port);
    }

    public function connect(array $servers)
    {
        if (!$this->isConnected()) {
            $this->servers = $servers;
            $server = $this->servers[0];
            $this->kestrel_api->pconnect($server['host'], $server['port']);
            $this->connected = true;
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function get($key, $reliable = false)
    {
        if ($reliable === true) {
            $key .= self::CMD_READ;
        }

        return $this->kestrel_api->get($key);
    }

    public function close($key)
    {
        return $this->kestrel_api->get($key . self::CMD_CLOSE);
    }

    public function abort($key)
    {
        return $this->kestrel_api->get($key . self::CMD_ABORT);
    }

    public function getNext($key) //always a reliable read. does a close and a get
    {
        return $this->kestrel_api->get($key . self::CMD_RELIABLE_READ);
    }

    public function peek($key)
    {
        return $this->kestrel_api->get($key . self::CMD_PEEK);
    }

    public function set($key, $value, $expire = 0)
    {
        return $this->kestrel_api->set($key, $value, $expire);
    }
}
