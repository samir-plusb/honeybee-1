<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Plugin;

/**
 * This plugin takes care of marking documents as deleted.
 */
class ArchivePlugin extends BasePlugin
{
    protected function doProcess()
    {
        $result = new Plugin\Result();

        try
        {
            $resource = $this->getResource();
            $service = $resource->getModule()->getService();

            $result->setState(Plugin\Result::STATE_OK);
            $result->setGate('promote');
            
            $this->logInfo(sprintf(
                "Successfully depublished and archived document: %s",
                $resource->getIdentifier()
            ));
        }
        catch(\Exception $e)
        {
            $this->logError(sprintf(
                "An error occured while depublishing/archiving document: %s\nError: %s",
                $workflowItem->getIdentifier(),
                $e->getMessage()
            ));

            $result->setState(Plugin\Result::OK);
            $result->setMessage($e->getMessage());
            $result->setGate('demote');
        }

        $result->freeze();
        
        return $result;
    }
}
