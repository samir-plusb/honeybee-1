<?php

/**
 * This plugin takes care of marking documents as deleted.
 *
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowPublishPlugin extends WorkflowBasePlugin
{
    protected function doProcess()
    {
        try
        {
            $resource = $this->getResource();

            $result = new WorkflowPluginResult();
            $result->setState(WorkflowPluginResult::STATE_OK);
            $result->setGate('promote');

            // @todo implement/hook in export strategy here.

            $this->logInfo(sprintf(
                "Successfully published document: %s to where ever ^^",
                $resource->getIdentifier()
            ));
        }
        catch(Exception $e)
        {
            $result = new WorkflowPluginResult();
            $result->setState(WorkflowPluginResult::STATE_OK);
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
