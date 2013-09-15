<?php

namespace Honeybee\Core\Queue\Job;

// @todo register shutdown listener to cleanup pid file
class JobQueueSpinner extends Runnable
{
    static protected $supported_signals = array(SIGHUP, SIGINT, SIGTERM, SIGQUIT, SIGUSR1, SIGUSR2);

    protected $stats;

    protected $worker_pool_size;

    protected $busy_worker_pids;

    protected $available_worker_pids;

    public function __construct($queue_name, $msg_queue_id, $ipc_channel)
    {
        parent::__construct($queue_name, $msg_queue_id, $ipc_channel);

        $this->busy_worker_pids = array();
        $this->available_worker_pids = array();
        $this->stats = array();
    }

    protected function setUp(array $parameters)
    {
        $this->writePidFile();
        $this->stats['started_at'] = new \DateTime();

        $this->spawnWorkers(isset($parameters['pool_size']) ? $parameters['pool_size'] : 3);
        $this->log("Finshed spawning workers, available pids: " . implode(',', $this->available_worker_pids));

        parent::setUp($parameters);
    }

    protected function spawnWorkers($num_workers)
    {
        $this->worker_pool_size = $num_workers;
        for ($i = 0; $i < $this->worker_pool_size; $i++)
        {
            $pid = pcntl_fork();
            if (-1 === $pid) {
                $this->running = false;
            } elseif (0 === $pid) {
                $worker = new JobQueueWorker($this->queue_name, $this->msg_queue_id, $this->ipc_channel);
                $worker->run();
                exit(0);
            } else {
                $this->available_worker_pids[] = $pid;
            }
        }
    }

    protected function tick(array $parameters)
    {
        while (
            $this->job_queue->hasJobs()
            && count($this->busy_worker_pids) < $this->worker_pool_size
        ) {
            $this->log("There is work to be done, lets notify a worker");
            $this->notifyFreeWorker();
        }
    }

    protected function notifyFreeWorker()
    {
        $avail_worker_pid = array_pop($this->available_worker_pids);
        $this->busy_worker_pids[] = $avail_worker_pid;
        posix_kill($avail_worker_pid, SIGUSR1);
        $this->log("Notified worker with pid: " . $avail_worker_pid);
    }

    protected function tearDown(array $parameters)
    {
        $this->removePidFile();
        parent::tearDown($parameters);
    }

    protected function getSupportedSignals()
    {
        return self::$supported_signals;
    }

    protected function onSignalReceived($signo)
    {
        switch ($signo) {
            case SIGUSR1:
                $this->log("... received SIGUSR1, checking job_queue for payload.");
                break;
            case SIGUSR2:
                $this->log("... received SIGUSR2, checking ipc_messaging and job_queue for payload.");
                if ($worker_message = $this->ipc_messaging->read()) {
                    $this->onWorkerFinished(json_decode($worker_message, true));
                }
                break;
            case SIGQUIT:
                $this->log("... received SIGQUIT, terminating!");
                $now = new \DateTime();
                $this->stats['uptime'] = $now->diff($this->stats['started_at']);
                $this->printStats();
                $this->running = false;
                break;
            case SIGINT:
            case SIGHUP:
            case SIGTERM:
                $this->log("... received SIGINT, SIGTERM or SIGHUP, terminating!");
                $this->running = false;
                break;
            default:
                $this->log("Received unhandled system signal. Ignoring: " . print_r($info, true));
        }
    }

    protected function onWorkerFinished(array $worker_response)
    {
        $worker_pid = $worker_response['worker_pid'];
        if ($worker_response['status'] === 'success') {
            $this->log("Received success notification from worker: " . $worker_pid);
        } else {
            $this->log("Received error notification from worker: " . $worker_pid);
        }
        $this->available_worker_pids[] = $worker_pid;
        $worker_index = array_search($worker_pid, $this->busy_worker_pids);
        array_splice($this->busy_worker_pids, $worker_index, 1);
    }

    protected function printStats()
    {
        $lines = array();
        $lines[] = PHP_EOL . "-- Honeybee jobqueue-spinner stats --";
        $lines[] = "   Started at: " . $this->stats['started_at']->format(\DateTime::ISO8601);
        $lines[] = "       Uptime: " . $this->stats['uptime']->format('%d days %H hours %I minutes');
        $lines[] = "Executed Jobs: " . $this->stats['executed_jobs'];
        $lines[] = "  Failed Jobs: " . $this->stats['failed_jobs'];
        $lines[] = "   Fatal Jobs: " . $this->stats['fatal_jobs'];
        echo PHP_EOL . implode(PHP_EOL, $lines) . PHP_EOL;
    }

    protected function writePidFile()
    {
        $pid = posix_getpid();
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';
        file_put_contents($pid_file, $pid);
    }

    protected function removePidFile()
    {
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';
        unlink($pid_file);
    }
}
