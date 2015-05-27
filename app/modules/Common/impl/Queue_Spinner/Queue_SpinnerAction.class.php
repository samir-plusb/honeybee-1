<?php

use Honeybee\Core\Job\Queue\Runnable\Spinner;
use Honeybee\Core\Job\Queue\Ipc\SignalSender;
use Honeybee\Core\Job\Queue\KestrelQueue;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    const DEFAULT_IPC_CHANNEL = 23;

    const ACTION_STATUS = 'status';

    const ACTION_START = 'start';

    const ACTION_STOP = 'stop';

    const ACTION_RESTART = 'restart';

    protected $signal_sender;

    public function initialize(AgaviExecutionContainer $container)
    {
        parent::initialize($container);

        $this->signal_sender = new SignalSender();
    }

    public function execute(AgaviRequestDataHolder $request_data)
    {
        $action = $request_data->getParameter('action', self::ACTION_STATUS);
        $queue_name = $request_data->getParameter(
            'queue',
            AgaviConfig::get('queue_spinner.default_queue')
        );
        $poolsize = $request_data->getParameter(
            'size',
            AgaviConfig::get('queue_spinner.default_max_workers')
        );
        $queue = new KestrelQueue($queue_name);

        switch ($action) {
            case self::ACTION_STATUS:
                $this->spinnerStatus($queue);
                break;
            case self::ACTION_START:
                $default_daemonize = AgaviConfig::get('queue_spinner.daemonize', false);
                $this->startSpinner($queue, $poolsize, $request_data->getParameter('daemonize', $default_daemonize));
                break;
            case self::ACTION_STOP:
                $this->stopSpinner($queue);
                break;
            case self::ACTION_RESTART:
                $this->restartSpinner($queue, $poolsize);
                break;
        }

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return false;
    }

    protected function startSpinner($queue, $worker_poolsize, $daemonize = false)
    {
        $spinner_pid = $this->signal_sender->getSpinnerPid($queue);

        if ($spinner_pid) {
            printf(PHP_EOL . "Spinner for queue '%s' already running." . PHP_EOL, $queue->getName());
            return;
        }

        $queue_name = $queue->getName();
        if ($daemonize) {
            $pid = pcntl_fork();
        }

        if ($daemonize === true && $pid === -1) {
            throw new RuntimeException("Unable to fork the job-queue spinner daemon.");
        } else if ($daemonize === true && $pid) {
            echo "Successfully forked the spinner process." . PHP_EOL;
        } else {
            // name of the (kestrel) queue to run the spinner for.
            $queue_name = $queue_name;
            // number of workers to spawn concurrently from within the spinner.
            $pool_size = $worker_poolsize;
            // one char long random identifier that is used when building System V keys (ftok)
            $msg_queue_id = 'H';
            // random int that is used as an identifier to filter ipc messaging
            $ipc_channel = AgaviConfig::get('queue_spinner.ipc_channel', self::DEFAULT_IPC_CHANNEL);

            $spinner = new Spinner($queue_name, $msg_queue_id, $ipc_channel);
            $spinner->run(array('pool_size' => $pool_size));
        }
    }

    protected function spinnerStatus($queue)
    {
        $spinner_pid = $this->signal_sender->getSpinnerPid($queue);

        if (!$spinner_pid) {
            printf(PHP_EOL . "Spinner for queue '%s' not running." . PHP_EOL, $queue->getName());
        } else {
            $display_file = AgaviConfig::get('queue_spinner.text_stats_file', 'queue_stats');

            if (!is_readable($display_file)) {
                throw new RuntimeException("Unable to read stats file at: " . $display_file);
            }

            $stats = file_get_contents($display_file);

            printf(
                PHP_EOL . "Stats for spinner (pid %d) running for queue: %s" . PHP_EOL,
                $spinner_pid,
                $queue->getName()
            );
            printf(PHP_EOL . $stats . PHP_EOL);
        }
    }

    protected function stopSpinner($queue)
    {
        $spinner_pid = $this->signal_sender->getSpinnerPid($queue);

        if (!$spinner_pid) {
            printf(PHP_EOL . "Spinner for queue '%s' not running." . PHP_EOL, $queue->getName());
        } else {
            $this->signal_sender->send($queue, SignalSender::TRIGGER_SHUTDOWN);
            echo PHP_EOL . "Waiting for spinner to stop ";

            while ($spinner_pid !== false) {
                $spinner_pid = $this->signal_sender->getSpinnerPid($queue);
                echo ".";
                sleep(1);
            }

            echo PHP_EOL . "Successfully stopped spinner" . PHP_EOL;
        }
    }

    protected function restartSpinner($queue, $poolsize)
    {
        $this->stopSpinner($queue);
        $this->startSpinner($queue, $poolsize);
    }
}
