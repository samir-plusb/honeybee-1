<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Queue;

class JobQueue extends Queue\FifoQueue
{
    public function push(Queue\IQueueItem $job)
    {
        if (!($job instanceof IJob))
        {
            throw new Exception(
                "The jobqeue only allows queuing of IJob implementations."
            );
        }

        $this->getClient()->set(
            $this->getName(), 
            array('type' => get_class($job), 'payload' => $job->toArray())
        );
    }

    public function shift()
    {
        $jobData = NULL;

        if (($jobData = parent::shift()))
        {
            return $this->createJob($jobData);
        }
        else
        {
            return $jobData;
        }
    }

    public function openNext()
    {
        $jobData = NULL;

        if (($jobData = parent::openNext()))
        {
            return $this->createJob($jobData);
        }
        else
        {
            return $jobData;
        }
    }

    protected function createJob(array $jobData)
    {
        if (!isset($jobData['type']))
        {
            throw new Exception("Unable to create job without type information.");
        }

        $jobClass = $jobData['type'];

        if (!class_exists($jobClass))
        {
            throw new Exception("Unable to resolve job implementor: " . $jobClass);
        }

        return new $jobClass($jobData['payload']);
    }
}
