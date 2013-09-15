<?php

use Honeybee\Core\Queue;
use Honeybee\Core\Queue\Job\JobQueueSpinner;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $parameters)
    {
        // name of the (kestrel) queue to run the spinner for.
        $queue_name = $parameters->getParameter('queue', 'prio:1-default_queue');
        // number of workers to spawn concurrently from within the spinner.
        $pool_size = $parameters->getParameter('size', 3);
        // one char long random identifier that is used when building System V keys (ftok)
        $msg_queue_id = 'H';
        // random int that is used as an identifier to filter ipc messaging
        $ipc_channel = 23;

        $spinner = new JobQueueSpinner($queue_name, $msg_queue_id, $ipc_channel);
        $spinner->run(array('pool_size' => $pool_size));

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
