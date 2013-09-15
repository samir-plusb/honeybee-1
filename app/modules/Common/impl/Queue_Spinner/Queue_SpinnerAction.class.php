<?php

use Honeybee\Core\Queue;
use Honeybee\Core\Queue\Job\JobQueueSpinner;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $parameters)
    {
        $spinner = new JobQueueSpinner();
        $spinner->start(
            $parameters->getParameter('queue', 'prio:1-default_queue'),
            $parameters->getParameter('size', 3)
        );

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
