<?php

namespace Honeybee\Core\Job\Queue;

use Honeybee\Core\Job\IJob;

class JobQueue extends FifoQueue
{
    public function push(IQueueItem $job)
    {
        if (!($job instanceof IJob)) {
            throw new Exception("The jobqeue only allows queueing of IJob implementations.");
        }

        $this->getClient()->set(
            $this->getName(),
            array(
                'type' => get_class($job),
                'payload' => $job->toArray()
            )
        );
        // trigger the spinner to inform it, that there is work to be done
        $signal_sender = new Ipc\SignalSender();
        $signal_sender->send($this, Ipc\SignalSender::TRIGGER_JOB_QUEUE);
    }

    public function shift()
    {
        $job_data = null;
        if (($job_data = parent::shift())) {
            return $this->createJob($job_data);
        } else {
            return $job_data;
        }
    }

    public function hasJobs()
    {
        return $this->getClient()->peek($this->getName()) !== false;
    }

    public function openNext()
    {
        $job_data = null;
        if (($job_data = parent::openNext())) {
            return $this->createJob($job_data);
        } else {
            return $job_data;
        }
    }

    protected function createJob(array $job_data)
    {
        if (!isset($job_data['type'])) {
            throw new Exception("Unable to create job without type information.");
        }
        $job_class = $job_data['type'];
        if (!class_exists($job_class)) {
            throw new Exception("Unable to resolve job implementor: " . $job_class);
        }

        return new $job_class($job_data['payload']);
    }
}
