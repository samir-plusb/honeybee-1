<?php

namespace Honeybee\Core\Queue\Kestrel;

class Client
{
    const CMD_ABORT = '/abort';

    const CMD_CLOSE = '/close';

    const CMD_READ = '/open';

    const CMD_RELIABLE_READ = '/close/open';

    const CMD_PEEK = '/peek';

    protected $connected = FALSE;
        
    protected $servers = array();
        
    protected $kestrelApi = NULL;

    public function __construct()
    {
        $this->kestrelApi = new \memcached;
    }
        
    public function addServer($host, $port)
    {
        $this->servers[] = array('host' => $host, 'port' => $port);
        $this->kestrelApi->addServer($host, $port);
    }
    
    public function connect(array $servers)
    {
        if (! $this->isConnected())
        {
            foreach ($servers as $server)
            {
                $this->addServer($server['host'], $server['port']);
            }

            $this->connected = TRUE;
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }
    
    public function get($key, $reliable = FALSE)
    {
        if (TRUE === $reliable)
        {
            $key .= self::CMD_READ;
        }
        
        return $this->kestrelApi->get($key);
    }
    
    public function close($key)
    {
        return $this->kestrelApi->get($key . self::CMD_CLOSE);
    }
    
    public function abort($key)
    {
        return $this->kestrelApi->get($key . self::CMD_ABORT);
    }
    
    public function getNext($key) //always a reliable read. does a close and a get
    {
        return unserialize($this->kestrelApi->get($key . self::CMD_RELIABLE_READ));
    }
    
    public function peek($key)
    {
        return $this->kestrelApi->get($key . self::CMD_PEEK);
    }
    
    public function set($key, $value, $expire = 0)
    {
        return $this->kestrelApi->set($key, $value, $expire);
    }
}
