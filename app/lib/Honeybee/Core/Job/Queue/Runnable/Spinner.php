<?php

namespace Honeybee\Core\Job\Queue\Runnable;

use Honeybee\Core\Job\IJob;
use Honeybee\Core\Config\ArrayConfig;
use RuntimeException;

// @todo register shutdown listener to notify parent process
class Spinner extends Runnable
{
    const LOG_INTERVAL = 1;

    const THROTTLE_TIMEOUT = 100000;

    static protected $supported_signals = array(SIGHUP, SIGINT, SIGTERM, SIGQUIT, SIGUSR1, SIGUSR2, SIGALRM);

    protected $worker_pool_size;

    protected $busy_worker_pids;

    protected $available_worker_pids;

    protected $stats_writer;

    protected $last_stats_write;

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

        $this->stats_writer = new SpinnerStatsWriter($this->buildStatsWriterConfig());
        pcntl_alarm(self::LOG_INTERVAL);

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
        while (($next_job = $this->job_queue->peek()) && (count($this->busy_worker_pids) < $this->worker_pool_size)) {
            $this->log("There is work to do, lets notify a worker ...");
            $this->pingNextFreeWorker($next_job);
        }
    }

    protected function pingNextFreeWorker(IJob $next_job)
    {
        $avail_worker_pid = array_pop($this->available_worker_pids);
        $this->busy_worker_pids[] = $avail_worker_pid;
        $this->log("... notified worker with pid: " . $avail_worker_pid);

        posix_kill($avail_worker_pid, SIGUSR1);
        $this->stats->onJobStarted();
        usleep(self::THROTTLE_TIMEOUT);
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
                // read any messages that are pending on the msg_queue
                // @todo as soon as workers send more than complete messages,
                // or when other components need to send stuff, we have to distinguish message types here.
                while (($worker_message = $this->ipc_messaging->read())) {
                    $this->onWorkerFinished(json_decode($worker_message, true));
                    $this->writeStats();
                }
                break;
            case SIGINT:
            case SIGHUP:
            case SIGTERM:
                $this->log("... received SIGINT, SIGTERM or SIGHUP, initiating graceful shutdown.");

                $this->shutDown();
                $this->writeStats();
                $this->running = false;
                break;
            case SIGALRM:
                pcntl_alarm(self::LOG_INTERVAL);
                $this->writeStats();
                break;
            default:
                $this->log("Received unhandled system signal. Ignoring: " . print_r($signo, true));
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
                sprintf("Received complete(error) notification from worker: %s\nError: %s", $worker_pid, @$worker_msg['error'])
            );
        }

        $worker_index = array_search($worker_pid, $this->busy_worker_pids);
        array_splice($this->busy_worker_pids, $worker_index, 1);
        array_unshift($this->available_worker_pids, $worker_pid);

        $this->stats->onJobFinished();
    }

    protected function writePidFile()
    {
        $pid = posix_getpid();
        $run_dir = realpath(\AgaviConfig::get('queue_spinner.run_dir'));
        $pid_file = $run_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';

        if (false === file_put_contents($pid_file, $pid)) {
            throw new RuntimeException("Unable to create spinner pid-file at location: " . $pid_file);
        }
    }

    protected function removePidFile()
    {
        $run_dir = realpath(\AgaviConfig::get('queue_spinner.run_dir'));
        $pid_file = $run_dir . DIRECTORY_SEPARATOR . 'queue.' . $this->queue_name . '.pid';

        unlink($pid_file);
    }

    protected function createStatsInstance()
    {
        return new SpinnerStats();
    }

    protected function shutDown()
    {
        $worker_pids = array_merge($this->busy_worker_pids, $this->available_worker_pids);
        for($i = 0; $i < count($worker_pids); $i++) {
            posix_kill($worker_pids[$i], SIGINT);
        }
        // @todo prevent restarts being blocked by long running workers:
        // - remove SIGALRM from the list of blocked signals `pcntl_sigprocmask(SIG_UNBLOCK, array(SIGALRM))`
        // - setup a `pcntl_alarm` callback before starting to wait for our children to shutdown.
        // - then force a shutdown with a `posix_kill($pid, SIGKILL)`,
        //   if they haven't terminated when the alarm callback kicks in.
        $status = null;
        while(($pid = pcntl_wait($status)) > 0) {
            $pid_pos = array_search($pid, $worker_pids);
            array_splice($worker_pids, $pid_pos, 1);
        }
        if (count($worker_pids) > 0) {
            $this->log(
                sprintf(
                    "The spinner is leaving %d worker-zombies behind. Zombie pids are: %s",
                    count($worker_pids),
                    implode(',', $worker_pids)
                )
            );
        } else {
            $this->log("Successfully stopped all worker processes.");
        }
    }

    protected function buildStatsWriterConfig()
    {
        $display_file = \AgaviConfig::get('queue_spinner.text_stats_file', 'queue_stats');
        $log_file = \AgaviConfig::get('queue_spinner.json_log_file', 'queue_stats.log.json');

        if (file_exists($log_file)) {
            unlink($log_file);
        }

        if (file_exists($display_file)) {
            unlink($display_file);
        }

        return new ArrayConfig(
            array('stats_display_file' => $display_file, 'stats_log_file' => $log_file)
        );
    }

    protected function writeStats()
    {
        $now = round(microtime(true) * 1000);
        if (!$this->last_stats_write) {
            $this->last_stats_write = $now;
        } elseif ($now - $this->last_stats_write >= 1000) {
            $this->last_stats_write = $now;
            $this->stats_writer->write($this->stats);
        }
    }
}
