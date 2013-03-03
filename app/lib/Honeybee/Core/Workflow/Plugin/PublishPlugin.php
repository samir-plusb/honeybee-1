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
        try
        {
            $resource = $this->getResource();

            $result = new Plugin\Result();
            $result->setState(Plugin\Result::STATE_OK);
            $result->setGate('promote');

            /*$queue = new JobQueue('prio:1-jobs');
            $jobData = array(
                'moduleClass' => get_class($resource->getModule()),
                'documentId' => $resource->getIdentifier()
            );
            $queue->push(new PublishJob($jobData));*/

            $this->logInfo(sprintf(
                "Successfully published document: %s to where ever ^^",
                $resource->getIdentifier()
            ));
        }
        catch(\Exception $e)
        {
            $result = new Plugin\Result();
            $result->setState(Plugin\Result::STATE_OK);
            $result->setMessage($e->getMessage());
            $result->setGate('demote');

            $this->logError(sprintf(
                "An error occured while deleting: %s from the database\n
                The couchdb client threw the following exception: \n%s",
                $this->getResource()->getIdentifier(),
                $e->getMessage()
            ));
        }

        $result->freeze();
        
        return $result;
    }
}
