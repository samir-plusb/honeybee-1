<?php

use Honeybee\Core\Queue;
use Honeybee\Core\Queue\Job\JobQueueSpinner;

class Common_Queue_SpinnerAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $parameters)
    {
        $queueName = $parameters->getParameter('queue', 'prio:1-jobs');
        
        $spinner = new JobQueueSpinner();
        $spinner->start($queueName);

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
