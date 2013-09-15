<?php

namespace Honeybee\Core\Queue\Job;

// @todo register shutdown listener to notify parent process
class JobQueueWorker extends Runnable
{
    static protected $supported_signals = array(SIGINT, SIGUSR1, SIGUSR2);

    protected $stats;

    public function __construct($queue_name, $msg_queue_id, $ipc_channel)
    {
        parent::__construct($queue_name, $msg_queue_id, $ipc_channel);

        $this->stats = array(
            'executed_jobs' => 0,
            'failed_jobs' => 0,
            'fatal_jobs' => 0,
            'started_at' => null,
            'uptime' => 0
        );
    }

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
                // no usage for this sig yet.
                $this->log("... received SIGUSR2.");
                break;
            default:
                $this->log("... received unhandled system signal. Ignoring: " . print_r($info, true));
        }
    }

    protected function runJob(IJob $job)
    {
        try {
            $this->log("Executing job-type: " . get_class($job));
            if (IJob::STATE_SUCCESS === $job->run()) {
                $this->onJobSuccess($job);
            } else {
                $this->onJobFailed($job);
            }
        } catch(Exception $e) {
            $this->onJobFailed($job);
        }
    }

    protected function onJobSuccess(IJob $job)
    {
        $this->job_queue->closeCurrent();

        $this->stats['executed_jobs']++;
        $this->log("Successfully executed job-type: " . get_class($job));
        $this->send(array('status' => 'success', 'worker_pid' => posix_getpid()), posix_getppid());
    }

    protected function onJobFailed(IJob $job)
    {
        $this->log("An error occured while executing job-type: " . get_class($job));
        $this->job_queue->closeCurrent();
        $notify_info = array('status' => 'error', 'worker_pid' => posix_getpid());

        if (IJob::STATE_FATAL !== $job->getState()) {
            $this->stats['failed_jobs']++;
            // @todo notify parent.
        } else {
            $this->stats['fatal_jobs']++;
            // @todo the job is now dropped from queue as fatal.
            // we might want to push it to an error queue
            // or to a journal for fatal jobs.
            $this->log("Dropping fatal job.");
            $notify_info['status'] = 'fatal';
        }
        $this->send($notify_info, posix_getppid());
    }
}
