<?php

namespace Honeybee\Core\Job\Queue\Spinner;

use Honeybee\Core\Job\Queue\Runnable\Runnable as BaseRunnable;
use Honeybee\Core\Job\Queue\Worker\Runnable as Worker;

// @todo register shutdown listener to notify parent process
class Runnable extends BaseRunnable
{
    const WORKER_RACE_DISTANCE = 250000;

    static protected $supported_signals = array(SIGHUP, SIGINT, SIGTERM, SIGQUIT, SIGUSR1, SIGUSR2);

    protected $worker_pool_size;

    protected $busy_worker_pids;

    protected $available_worker_pids;

    public function __construct($queue_name, $msg_queue_id, $ipc_channel)
    {
        $this->busy_worker_pids = array();
        $this->available_worker_pids = array();

        parent::__construct($queue_name, $msg_queue_id, $ipc_channel);
    }

    protected function setUp(array $parameters)
    {
        $this->writePidFile();
        $this->spawnWorkers(isset($parameters['pool_size']) ? $parameters['pool_size'] : 3);

        parent::setUp($parameters);
    }

    protected function spawnWorkers($num_workers)
    {
        $this->worker_pool_size = $num_workers;
        for ($i = 0; $i < $this->worker_pool_size; $i++) {
            $pid = pcntl_fork();
            if (-1 === $pid) {
                $this->running = false;
            } elseif (0 === $pid) {
                $worker = new Worker($this->queue_name, $this->msg_queue_id, $this->ipc_channel);
                $worker->run();
                exit(0);
            } else {
                $this->available_worker_pids[] = $pid;
            }
        }

        $this->log("Finshed spawning workers, available pids: " . implode(',', $this->available_worker_pids));
    }

    protected function tick(array $parameters)
    {
        while ($this->job_queue->hasJobs() && count($this->busy_worker_pids) < $this->worker_pool_size) {
            $this->log("There is work to do, lets notify a worker ...");
            $this->notifyFreeWorker();
        }

        $this->printStats();
    }

    protected function notifyFreeWorker()
    {
        $avail_worker_pid = array_pop($this->available_worker_pids);
        $this->busy_worker_pids[] = $avail_worker_pid;
        $this->log("... notified worker with pid: " . $avail_worker_pid);

        posix_kill($avail_worker_pid, SIGUSR1);
        // give the worker a sec. to pull the job off the queue in order to minimize worker races.
        usleep(self::WORKER_RACE_DISTANCE);
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
            case SIGINT:
            case SIGHUP:
            case SIGTERM:
                $this->log("... received SIGINT, SIGTERM or SIGHUP, initiating graceful shutdown.");
                // @todo $this->shutDown();
                $this->running = false;
                break;
            default:
                $this->log("Received unhandled system signal. Ignoring: " . print_r($info, true));
        }
    }

    protected function onWorkerFinished(array $worker_msg)
    {
        $worker_pid = $worker_msg['worker_pid'];
        if (isset($worker_msg['stats'])) {
            $this->stats->setWorkerStats($worker_msg['worker_pid'], $worker_msg['stats']);
        }

        if ($worker_msg['status'] === 'success') {
            $this->log("Received complete(success) notification from worker: " . $worker_pid);
        } else {
            $this->log(
                sprintf("Received complete(error) notification from worker: %s\nError: %s", $worker_pid, $worker_msg['error'])
            );
        }

        $worker_index = array_search($worker_pid, $this->busy_worker_pids);
        array_splice($this->busy_worker_pids, $worker_index, 1);
        array_unshift($this->available_worker_pids, $worker_pid);
    }

    protected function printStats()
    {
        $stats_array = $this->stats->toArray();
        $now = new \DateTime();
        $uptime = $now->diff(new \DateTime($stats_array['start_time']));

        $lines = array();
        $lines[] = '# SPINNER STATS #';
        $lines[] = "   Started at: " . $stats_array['start_time'];
        $lines[] = "       Uptime: " . $uptime->format('%d days %H hours %I minutes') . PHP_EOL;
        $lines[] = '# WORKER STATS #';
        foreach ($stats_array['worker_stats'] as $worker_pid => $worker_stats) {
            $worker_started = $worker_stats['start_time'];
            if (isset($worker_stats['start_time']['date'])) {
                $worker_started = $worker_stats['start_time']['date'];
            }

            $uptime = $now->diff(new \DateTime($worker_started));
            $lines[] = PHP_EOL . '-- Worker [' . $worker_pid . ']';
            $lines[] = "     Started at: " . $worker_started;
            $lines[] = "         Uptime: " . $uptime->format('%d days %H hours %I minutes');
            $lines[] = "   Started Jobs: " . $worker_stats['started_jobs'];
            $lines[] = "Successful Jobs: " . $worker_stats['successful_jobs'];
            $lines[] = "    Failed Jobs: " . $worker_stats['failed_jobs'];
            $lines[] = "     Fatal Jobs: " . $worker_stats['fatal_jobs'];
        }

        file_put_contents('queue.stats', implode(PHP_EOL, $lines));
    }

    protected function writePidFile()
    {
        $pid = posix_getpid();
        $base_dir = \AgaviConfig::get('queue.run_dir', dirname(\AgaviConfig::get('core.app_dir')));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';
        file_put_contents($pid_file, $pid);
    }

    protected function removePidFile()
    {
        $base_dir = \AgaviConfig::get('queue.run_dir', dirname(\AgaviConfig::get('core.app_dir')));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';

        unlink($pid_file);
    }

    protected function createStatsInstance()
    {
        return new Stats();
    }
}
