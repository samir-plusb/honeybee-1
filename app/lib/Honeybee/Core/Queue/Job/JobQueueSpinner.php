<?php

namespace Honeybee\Core\Queue\Job;

// @todo register shutdown listener to cleanup pid file
class JobQueueSpinner
{
    const IPC_CHANNEL = 23;

    private static $supported_signals = array(
        SIGHUP, SIGINT, SIGTERM, SIGQUIT, SIGUSR1, SIGUSR2
    );

    private $running;

    private $stats;

    private $job_queue;

    private $ipc_messaging;

    private $worker_pool_size;

    private $busy_worker_pids;

    private $available_worker_pids;

    public function __construct()
    {
        $this->running = false;
        $this->busy_worker_pids = array();
        $this->available_worker_pids = array();
        $this->stats = array();
    }

    public function start($queue_name, $pool_size = 3)
    {
        if ($this->running === true) {
            return;
        }

        declare(ticks = 1);

        // block interupting signals, to allow for graceful shutdowns
        // thereby letting a current loopcycle complete before terminating.
        pcntl_sigprocmask(SIG_BLOCK, self::$supported_signals);
        $this->log("Started jobqueue spinner with pid: " . posix_getpid());

        $this->worker_pool_size = $pool_size;
        $this->stats['started_at'] = new \DateTime();
        $this->running = true;

        $this->writePidFile($queue_name);
        $this->spawnWorkers($queue_name);
        $this->log("Finshed spawning workers, available pids: " . implode(',', $this->available_worker_pids));

        $this->job_queue = new JobQueue($queue_name);
        $this->initIpcMessaging($queue_name);
        $this->run();

        $this->removePidFile($queue_name);
        $this->log("The spinner process is going to die now.");
        $this->running = false;
        $this->ipc_messaging->destroy();
    }

    protected function spawnWorkers($queue_name)
    {
        for ($i = 0; $i < $this->worker_pool_size; $i++)
        {
            $pid = pcntl_fork();
            if (-1 === $pid) {
                $this->running = false;
            } elseif (0 === $pid) {
                $worker = new JobQueueWorker($queue_name, self::IPC_CHANNEL);
                $worker->start();
                exit(0);
            } else {
                $this->available_worker_pids[] = $pid;
            }
        }
    }

    protected function initIpcMessaging($queue_name)
    {
        $queue_path_parts = array(
            dirname(\AgaviConfig::get('core.app_dir')),
            'etc',
            'local',
            $queue_name . '.msg_q'
        );
        $queue_path = implode(DIRECTORY_SEPARATOR, $queue_path_parts);
        $this->ipc_messaging = new IpcMessaging($queue_path, 'H', self::IPC_CHANNEL);
    }

    protected function run()
    {
        while ($this->running) {
            while (
                $this->job_queue->hasJobs()
                && count($this->busy_worker_pids) < $this->worker_pool_size
            ) {
                $this->log("There is work to be done, lets notify a worker");
                $this->notifyFreeWorker();
            }
            $this->log("Waiting for next system signal ...");
            $this->handleSignal(pcntl_sigwaitinfo(self::$supported_signals));
        }
    }

    protected function notifyFreeWorker()
    {
        $avail_worker_pid = array_pop($this->available_worker_pids);
        $this->busy_worker_pids[] = $avail_worker_pid;
        posix_kill($avail_worker_pid, SIGUSR1);
        $this->log("Notified worker with pid: " . $avail_worker_pid);
    }

    protected function handleSignal($signo)
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

    protected function log($message)
    {
        $now = new \DateTime();
        error_log(
            sprintf('[%s][%s] %s', __CLASS__, $now->format('h:i:s.u'), $message)
        );
    }

    protected function writePidFile($queue_name)
    {
        $pid = posix_getpid();
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $queue_name . '.pid';

        file_put_contents($pid_file, $pid);
    }

    protected function removePidFile($queue_name)
    {
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $queue_name . '.pid';

        unlink($pid_file);
    }
}
