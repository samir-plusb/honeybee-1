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

            /* @todo Reintegrate when we introduce queue in production:
            $queue = new JobQueue('prio:1-jobs');
            $jobData = array(
                'moduleClass' => get_class($resource->getModule()),
                'documentId' => $resource->getIdentifier(),
                'exports' => $this->getParameter('exports')
            );
            $queue->push(new PublishJob($jobData));
            $result->setState(Plugin\Result::STATE_EXPECT_INPUT);*/
            
            $module = $resource->getModule();

            $exports = $this->getParameter('exports');
            $exports = is_array($exports) ? $exports : array();

            $exportService = $module->getService('export');

            foreach ($exports as $exportName)
            {
                $exportService->publish($exportName, $resource);
            }
            
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
var_dump($e->getMessage());exit;
            $result->setState(Plugin\Result::STATE_OK);
            $result->setMessage($e->getMessage());
            $result->setGate('demote');
        }

        $result->freeze();
        
        return $result;
    }
}
