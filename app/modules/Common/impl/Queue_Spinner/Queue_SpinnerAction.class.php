<?php

use Honeybee\Core\Queue;
use Honeybee\Core\Queue\Job\JobQueueSpinner;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $parameters)
    {
        $spinner = new JobQueueSpinner(
            $parameters->getParameter('queue', 'prio:1-jobs')
        );
        $spinner->start();

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
