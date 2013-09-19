<?php

namespace Honeybee\Core\Job\Queue\Runnable;

use Honeybee\Core\Job\IJob;

// @todo register shutdown listener to notify parent process
class Worker extends Runnable
{
    const IPC_STATS_CHANNEL = 42;

    static protected $supported_signals = array(SIGINT, SIGUSR1, SIGUSR2);

    protected function getSupportedSignals()
    {
        return self::$supported_signals;
    }

    protected function onSignalReceived($signo)
    {
        switch ($signo) {
            case SIGINT:
                $this->log("... received SIGINT, terminating ...");

                $this->running = false;
                break;
            case SIGUSR1:
                $this->log("... received SIGUSR1, will look for a job.");

                if ($job = $this->job_queue->shift()) {
                    $this->runJob($job);
                } else {
                    $this->log("Didn't find a job.");

                    $this->ipc_messaging->send(
                        json_encode(array('status' => 'idle', 'worker_pid' => posix_getpid()))
                    );
                    posix_kill(posix_getppid(), SIGUSR2);
                }
                break;
            case SIGUSR2:
                $this->log("... received SIGUSR2, will send current stats to work.");

                $this->sendStats();
                break;
            default:
                $this->log("... received unhandled system signal. Ignoring: " . print_r($info, true));
        }
    }

    protected function runJob(IJob $job)
    {
        try {
            $this->log("Executing job-type: " . get_class($job));

            $this->stats->onJobStarted($job);
            if (IJob::STATE_SUCCESS === $job->run()) {
                $this->onJobSuccess($job);
            } else {
                $this->onJobFailed($job);
            }
        } catch(\Exception $e) {
            $this->onJobFailed($job, $e->getMessage());
        }
    }

    protected function onJobSuccess(IJob $job)
    {
        $this->job_queue->closeCurrent();
        $this->stats->onJobSuccess($job);

        $this->log("Successfully executed job-type: " . get_class($job));

        $this->send(
            array(
                'type' => 'job-complete',
                'status' => 'success',
                'worker_pid' => posix_getpid(),
                'stats' => $this->stats->toArray()
            ),
            posix_getppid()
        );
    }

    protected function onJobFailed(IJob $job, $error_message = '')
    {
        $this->job_queue->closeCurrent();

        $this->log("An error occured while executing job-type: " . get_class($job));

        $status = 'error';
        if (IJob::STATE_FATAL === $job->getState()) {
            $this->stats->onJobFatal($job);
            $status = 'fatal';
            $this->log("Dropping fatal job.");
            // @todo the job is now dropped from queue as fatal.
            // we might want to push it to an error queue
            // or to a journal for fatal jobs.
        } else {
            $this->job_queue->push($job);
            $this->stats->onJobError($job);
        }

        $notify_info = array(
            'type' => 'job-complete',
            'status' => $status,
            'worker_pid' => posix_getpid(),
            'error' => $error_message,
            'stats' => $this->stats->toArray()
        );
        $this->send($notify_info, posix_getppid());
    }

    protected function createStatsInstance()
    {
        return new WorkerStats();
    }
}
