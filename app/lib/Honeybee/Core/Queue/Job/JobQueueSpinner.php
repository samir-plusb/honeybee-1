<?php

namespace Honeybee\Core\Queue\Job;

class JobQueueSpinner
{
    private $run = FALSE;

    public function start($queueName)
    {
        $this->running = TRUE;

        while ($this->running)
        {
            $pid = pcntl_fork();

            if (-1 === $pid)
            {
                $this->running = FLASE;
            }
            elseif (0 === $pid)
            {
                $queue = new JobQueue($queueName);

                if ($job = $queue->shift())
                {
                    $this->runJob($queue, $job);
                }

                exit(0);
            }
            else
            {
                pcntl_waitpid($pid, $status);
            }

            // @todo we should register to sig* in order to
            // in order to kill zombie children and try grafefull shutdown.

            sleep(1); 
        }
    }

    protected function runJob($queue, IJob $job)
    {
        try
        {
            if (IJob::STATE_SUCCESS === $job->run())
            {
                $queue->closeCurrent();
            }
            else
            {
                $this->handleJobError($queue, $job);
            }
        }
        catch(Exception $e)
        {
            $this->handleJobError($queue, $job);
        }
    }

    protected function handleJobError($queue, IJob $job)
    {
        $queue->closeCurrent();
        
        if (IJob::STATE_FATAL !== $job->getState())
        {
            $queue->push($job);
        }
        else
        {
            // @todo the job is now dropped from queue as fatal.
            // we might want to push it to an error queue
            // or to a journal for fatal jobs.
            echo "Dropping fatal job.\n";
        }
    }
}
