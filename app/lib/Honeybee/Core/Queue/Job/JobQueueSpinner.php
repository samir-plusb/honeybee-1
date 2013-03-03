<?php

namespace Honeybee\Core\Queue\Job;

class JobQueueSpinner
{
    private $queue;

    private $run = FALSE;

    public function __construct($queueName)
    {
        $this->queue = new JobQueue($queueName);
    }

    public function start()
    {
        $this->run = TRUE;

        while ($this->run)
        {
            if ($job = $this->queue->openNext())
            {
                try
                {
                    if (IJob::STATE_SUCCESS === $job->run())
                    {
                        $this->queue->closeCurrent();
                        echo "Memory: " . (memory_get_usage(TRUE) / 1048576) . PHP_EOL;
                    }
                    else
                    {
                        $this->handleJobError($job);
                    }
                }
                catch(Queue\Exception $e)
                {
                    if (NULL !== $job)
                    {
                        $this->handleJobError($job);
                    }
                }
            }

            sleep(1);
        }
    }

    protected function handleJobError(IJob $job)
    {
        // @todo inspect retry constraints and then decide whether to repush or drop as fatal.
        echo "[" . get_class($this) . "] Job had an error... Readding to queue." . PHP_EOL;
        $this->queue->push($job);
    }
}
