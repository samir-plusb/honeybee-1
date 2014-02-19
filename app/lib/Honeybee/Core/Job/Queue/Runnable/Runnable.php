<?php

namespace Honeybee\Core\Job\Queue\Runnable;

use Honeybee\Core\Job\Queue\KestrelQueue;
use Honeybee\Core\Job\Queue\Ipc\IpcMessaging;
use DateTime;
use AgaviConfig;

abstract class Runnable implements IRunnable
{
    protected $running = false;

    protected $msg_queue_id;

    protected $ipc_channel;

    protected $ipc_messaging;

    protected $queue_name;

    protected $job_queue;

    protected $stats;

    protected abstract function getSupportedSignals();

    protected abstract function onSignalReceived($signo);

    public function __construct($queue_name, $msg_queue_id, $ipc_channel)
    {
        $this->running = false;
        $this->queue_name = $queue_name;
        $this->msg_queue_id = $msg_queue_id;
        $this->ipc_channel = $ipc_channel;
        $this->stats = $this->createStatsInstance();
    }

    public function run(array $parameters = array())
    {
        if ($this->running === true) {
            return false;
        }

        declare(ticks = 1);

        $this->running = true;
        pcntl_sigprocmask(SIG_BLOCK, $this->getSupportedSignals());

        $this->stats->onRunnableStarted();
        $this->setUp($parameters);
        $this->log(sprintf("Started runnable with pid: %d (%s)", posix_getpid(), get_class($this)));

        while ($this->running) {
            $this->tick($parameters);
            $system_signal = pcntl_sigwaitinfo($this->getSupportedSignals());
            $this->onSignalReceived($system_signal);
        }

        $this->running = false;
        $this->tearDown($parameters);
        $this->stats->onRunnableStopped();
        $this->log(sprintf("Stopped runnable with pid: %d (%s)", posix_getpid(), get_class($this)));
    }

    protected function setUp(array $parameters)
    {
        $this->initIpcMessaging($this->queue_name, $this->ipc_channel);
        $this->job_queue = new KestrelQueue($this->queue_name);
    }

    protected function tearDown(array $parameters)
    {
        $this->ipc_messaging->destroy();
    }

    protected function tick(array $parameters)
    {
    }

    protected function initIpcMessaging()
    {
        $queue_path_parts = array(
            dirname(AgaviConfig::get('core.app_dir')),
            'etc',
            'local',
            $this->queue_name . '.msg_q'
        );
        $queue_path = implode(DIRECTORY_SEPARATOR, $queue_path_parts);
        $this->ipc_messaging = new IpcMessaging($queue_path, $this->msg_queue_id, $this->ipc_channel);
    }

    protected function send(array $data, $receiver_pid, $msg_type = null)
    {
        $this->ipc_messaging->send(json_encode($data), $msg_type);
        posix_kill($receiver_pid, SIGUSR2);
    }

    protected function createStatsInstance()
    {
        return new Stats();
    }

    protected function log($message)
    {
        $now = new DateTime();
        error_log(
            sprintf('[%s][%s][%s] %s', get_class($this), posix_getpid(), $now->format('h:i:s.u'), $message)
        );
    }
}
