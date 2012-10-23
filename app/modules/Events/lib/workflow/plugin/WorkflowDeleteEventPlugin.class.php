<?php

/**
 * This plugin takes care of marking Events as deleted.
 *
 * @author tay
 * @version $Id$
 * @package Events
 * @subpackage Workflow/Plugin
 */
class WorkflowDeleteEventPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'event_delete';

    const GATE_TRASH = 'trash';

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    /**
     * (non-PHPdoc)
     * @see WorkflowBasePlugin::process()
     */
    protected function doProcess()
    {
        $result = new WorkflowPluginResult();

        try
        {
            $workflowService = EventsWorkflowService::getInstance();
            $workflowItem = $workflowService->fetchWorkflowItemById($this->ticket->getItem());
            $workflowService->deleteWorkflowItem($workflowItem);

            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setGate(self::GATE_TRASH);
            $this->logInfo(sprintf(
                "Successfully moved (Events)item: %s to the trash",
                $workflowItem->getIdentifier()
            ));
            // @todo sync deletion to frontend here ...            
        }
        catch(CouchdbClientException $e)
        {
            $result->setState(WorkflowPluginResult::STATE_ERROR);
            $result->setMessage($e->getMessage());

            $this->logError(sprintf(
                "An error occured while deleting movie: %s from the database\n
                The couchdb client threw the following exception: \n%s",
                $workflowItem->getIdentifier(),
                $e->getMessage()
            ));
        }

        $result->freeze();
        return $result;
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        // @todo Add MayDeleteCredential
        return TRUE;
    }
}

?>
