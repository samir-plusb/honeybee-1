<?php

use Honeybee\Core\Job\Queue\Runnable\Spinner;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $request_data)
    {
        // name of the (kestrel) queue to run the spinner for.
        $queue_name = $request_data->getParameter('queue', 'prio:1-default_queue');
        // number of workers to spawn concurrently from within the spinner.
        $pool_size = $request_data->getParameter('size', 3);
        // one char long random identifier that is used when building System V keys (ftok)
        $msg_queue_id = 'H';
        // random int that is used as an identifier to filter ipc messaging
        $ipc_channel = 23;

        $spinner = new Spinner($queue_name, $msg_queue_id, $ipc_channel);
        $spinner->run(array('pool_size' => $pool_size));

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
