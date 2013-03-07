<?php

namespace Honeybee\Core\Queue;

use Honeybee\Core\Queue\Kestrel;

class FifoQueue implements IQueue
{
    private $client;

    private $name;

    public function __construct($name)
    {
        $this->name = $name;

        // @todo make the client exchangeable by introducing a client interface
        // and injecting instead of creating the client.
        $this->client = new Kestrel\Client();
        // @todo introduce client configuration
        $this->client->connect(array(
            array('host' => 'localhost', 'port' => '22133')
        ));
    }

    public function shift()
    {
        return unserialize($this->client->getNext($this->name));
    }

    public function getName()
    {
        return $this->name;
    }

    public function push(IQueueItem $item)
    {
        $this->client->set($this->name, $item->toArray());
    }

    public function openNext()
    {
        return unserialize($this->client->get($this->name));
    }

    public function closeCurrent()
    {
        return $this->client->close($this->name);
    }

    public function abortCurrent()
    {
        return $this->client->abort($this->name);
    }

    protected function getClient()
    {
        return $this->client;
    }
}
