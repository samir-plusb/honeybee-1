<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Plugin;

/**
 * This plugin takes care of marking documents as deleted.
 */
class DeletePlugin extends BasePlugin
{
    protected function doProcess()
    {
        try
        {
            $resource = $this->getResource();
            $service = $resource->getModule()->getService();
            $service->delete($resource);

            $result = new Plugin\Result();
            $result->setState(Plugin\Result::STATE_EXPECT_INPUT);
            $result->setGate('suspend');
            
            $this->logInfo(sprintf(
                "Successfully moved document: %s to the trash",
                $resource->getIdentifier()
            ));
        }
        catch(\Exception $e)
        {
            $result = new Plugin\Result();
            $result->setState(Plugin\Result::STATE_ERROR);
            $result->setMessage($e->getMessage());

            $this->logError(sprintf(
                "An error occured while deleting: %s from the database\n
                The couchdb client threw the following exception: \n%s",
                $workflowItem->getIdentifier(),
                $e->getMessage()
            ));
        }

        $result->freeze();
        
        return $result;
    }
}
