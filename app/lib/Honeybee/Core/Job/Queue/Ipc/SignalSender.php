<?php

namespace Honeybee\Core\Job\Queue\Ipc;

use Honeybee\Core\Job\Queue\Exception;
use Honeybee\Core\Job\Queue\JobQueue;

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

    public function send(JobQueue $queue, $signo)
    {
        $spinner_pid = $this->getSpinnerPid($queue);
        if ($spinner_pid && $spinner_pid !== posix_getpid()) {
            posix_kill($spinner_pid, $signo);
        }
    }

    protected function isValidSignal($signo)
    {
        return in_array($signo, self::$supported_signals);
    }

    protected function getSpinnerPid(JobQueue $queue)
    {
        $base_dir = dirname(\AgaviConfig::get('core.app_dir'));
        $pid_file = $base_dir . DIRECTORY_SEPARATOR . 'queue.' . $queue->getName() . '.pid';
        if (! is_readable($pid_file)) {
            throw new Exception("Unable to find pid file for jobqueue spinner.");
        }
        $pid = (int)file_get_contents($pid_file);

        return $pid > 0 ? $pid : false;
    }
}
