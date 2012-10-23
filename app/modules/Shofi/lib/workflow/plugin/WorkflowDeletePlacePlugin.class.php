<?php

/**
 * This plugin takes care of marking shofi places as deleted.
 *
 * @author tay
 * @version $Id$
 * @package Shofi
 * @subpackage Workflow/Plugin
 */
class WorkflowDeletePlacePlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'delete_place';

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
            $shofiService = ShofiWorkflowService::getInstance();
            $workflowItem = $shofiService->fetchWorkflowItemById($this->ticket->getItem());
            $shofiService->deleteWorkflowItem($workflowItem);

            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setGate(self::GATE_TRASH);
            $this->logInfo(sprintf(
                "Successfully moved (shofi place)item: %s to the trash",
                $workflowItem->getIdentifier()
            ));

            $exportAllowed = AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED);
            $method = $this->ticket->getExecutionContainer()->getRequestMethod();
            if ($exportAllowed && 'write' === $method)
            {
                $cmExport = new ContentMachineHttpExport(
                    AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
                );
                // @todo we need a try catch here,
                // so the import does not break just because the conentmachine is not reachable.
				if (! $cmExport->deleteEntity($workflowItem->getIdentifier(), 'place'))
                {
                    $this->logInfo(sprintf(
                        "An error occured while sending delete post to the contentmachine:\n%s",
                        print_r($cmExport->getLastErrors(), TRUE)
                    ));
                }
				else
				{
					$this->logInfo("Successfully sent request to the contentmachine.");	
				}
            }
        }
        catch(CouchdbClientException $e)
        {
            $result->setState(WorkflowPluginResult::STATE_ERROR);
            $result->setMessage($e->getMessage());

            $this->logError(sprintf(
                "An error occured while deleting shofi place: %s from the database\n
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
