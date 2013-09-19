<?php

namespace Honeybee\Core\Job\Queue;

use Honeybee\Core\Job\IJob;

class KestrelQueue implements IFifoQueue
{
    private $client;

    private $name;

    public function __construct($name)
    {
        $this->name = $name;
        $this->client = \AgaviContext::getInstance()->getDatabaseConnection('Queue.Write');
    }

    public function shift()
    {
        $job_state = unserialize($this->client->get($this->name));
        if ($job_state) {
            return $this->createJob($job_state);
        } else {
            return null;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function push(IQueueItem $item)
    {
        if (!($item instanceof IJob)) {
            throw new Exception("The jobqeue only allows queueing of IJob implementations.");
        }

        $this->client->set(
            $this->getName(),
            array('type' => get_class($item), 'payload' => $item->toArray())
        );
        // trigger the spinner to inform it, that there is work to be done
        $signal_sender = new Ipc\SignalSender();
        $signal_sender->send($this, Ipc\SignalSender::TRIGGER_JOB_QUEUE);
    }

    public function openNext()
    {
        $job_state = unserialize($this->client->get($this->name));
        if ($job_state) {
            return $this->createJob($job_state);
        } else {
            return null;
        }
    }

    public function closeCurrent()
    {
        return $this->client->close($this->name);
    }

    public function abortCurrent()
    {
        return $this->client->abort($this->name);
    }

    public function hasItems()
    {
        return $this->client->peek($this->getName()) !== false;
    }

    protected function createJob(array $job_state)
    {
        if (!isset($job_state['type'])) {
            throw new Exception("Unable to create job without type information.");
        }
        $job_class = $job_state['type'];
        if (!class_exists($job_class)) {
            throw new Exception("Unable to resolve job implementor: " . $job_class);
        }

        return new $job_class($job_state['payload']);
    }
}
