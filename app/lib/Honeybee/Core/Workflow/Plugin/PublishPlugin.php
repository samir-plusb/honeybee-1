<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Plugin;
use Honeybee\Core\Queue\Job\PublishJob;
use Honeybee\Core\Queue\Job\JobQueue;

/**
 * This plugin takes care of marking documents as deleted.
 */
class PublishPlugin extends BasePlugin
{
    protected function doProcess()
    {
        $result = new Plugin\Result();

        try
        {
            $resource = $this->getResource();

            /*$queue = new JobQueue('prio:1-jobs');
            $jobData = array(
                'moduleClass' => get_class($resource->getModule()),
                'documentId' => $resource->getIdentifier()
            );
            $queue->push(new PublishJob($jobData));

            $result->setState(Plugin\Result::STATE_EXPECT_INPUT);
            $result->setGate('promote');*/

            $result->setState(Plugin\Result::STATE_OK);
            $result->setGate('promote');

            $this->logInfo(sprintf(
                "Successfully queued publish job for document: %s",
                $resource->getIdentifier()
            ));
        }
        catch(\Exception $e)
        {
            $this->logError(sprintf(
                "An error occured while publishing document: %s\nError: %s\n",
                $this->getResource()->getIdentifier(),
                $e->getMessage()
            ));

            $result->setState(Plugin\Result::STATE_OK);
            $result->setMessage($e->getMessage());
            $result->setGate('demote');
        }

        $result->freeze();
        
        return $result;
    }
}
