<?php

namespace Honeybee\Core\Job\Queue\Ipc;

use Honeybee\Core\Job\Queue\Exception;
use Honeybee\Core\Job\Queue\IQueue;

class SignalSender
{
    const TRIGGER_SHUTDOWN = 2;

    const TRIGGER_JOB_QUEUE = 10;

    const TRIGGER_MSG_QUEUE = 12;

    private static $supported_signals = array(
        self::TRIGGER_SHUTDOWN,
        self::TRIGGER_JOB_QUEUE,
        self::TRIGGER_MSG_QUEUE
    );

    public function send(IQueue $queue, $signo)
    {
        $spinner_pid = $this->getSpinnerPid($queue);
        if ($spinner_pid && $spinner_pid !== posix_getpid()) {
            posix_kill($spinner_pid, $signo);
        }
    }

    public function getSpinnerPid(IQueue $queue)
    {
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $run_dir = realpath(\AgaviConfig::get('queue_spinner.run_dir'));
        $pid_file = $run_dir . DIRECTORY_SEPARATOR . 'queue.' . $queue->getName() . '.pid';
        $pid = false;

        if (is_readable($pid_file)) {
            $pid = (int)file_get_contents($pid_file);
            if (!file_exists( "/proc/" . $pid)) {
                $pid = false;
            }
        }

        return $pid;
    }

    protected function isValidSignal($signo)
    {
        return in_array($signo, self::$supported_signals);
    }
}
