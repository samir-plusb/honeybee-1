<?php

/**
 * This plugin takes care of marking documents as deleted.
 *
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowArchivePlugin extends WorkflowBasePlugin
{
    protected function doProcess()
    {
        try
        {
            $resource = $this->getResource();
            $service = $resource->getModule()->getService();

            $result = new WorkflowPluginResult();
            $result->setState(WorkflowPluginResult::STATE_OK);
            $result->setGate('promote');
            
            $this->logInfo(sprintf(
                "Successfully depublished and archived document: %s",
                $resource->getIdentifier()
            ));
        }
        catch(Exception $e)
        {
            $result = new WorkflowPluginResult();
            $result->setState(WorkflowPluginResult::OK);
            $result->setMessage($e->getMessage());
            $result->setGate('demote');

            $this->logError(sprintf(
                "An error occured while depublishing/archiving document: %s\nError: %s",
                $workflowItem->getIdentifier(),
                $e->getMessage()
            ));
        }

        $result->freeze();
        
        return $result;
    }
}
