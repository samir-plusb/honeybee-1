<?php

/**
 * This plugin takes care of marking news as deleted.
 *
 * @author tay
 * @version $Id$
 * @package News
 * @subpackage Workflow/Plugin
 */
class WorkflowDeleteNewsPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'delete_news';

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
            $newsService = NewsWorkflowService::getInstance();
            $workflowItem = $newsService->fetchWorkflowItemById($this->ticket->getItem());
            $newsService->deleteWorkflowItem($workflowItem);

            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setGate(self::GATE_TRASH);
            $this->logInfo(sprintf(
                "Successfully moved (news)item: %s to the trash",
                $workflowItem->getIdentifier()
            ));
        }
        catch(CouchdbClientException $e)
        {
            $result->setState(WorkflowPluginResult::STATE_ERROR);
            $result->setMessage($e->getMessage());

            $this->logError(sprintf(
                "An error occured while deleting item: %s from the database\n
                The couchdb client threw the following exception: \n%s",
                $workflowItem->getIdentifier(),
                $e->getMessage()
            ));
        }

        if (TRUE === AgaviConfig::get('news.frontend_sync', FALSE))
        {
            try
            {
                $feClient = new FrontendApiClient();
                $feClient->deleteWorkflowItem($workflowItem);

                $result->setMessage('Successfully sent delete notification to frontend.');
                $this->logInfo(sprintf(
                    "Successfully sent delete notification to frontend.",
                    $workflowItem->getIdentifier()
                ));
            }
            catch(FrontendApiClientException $e)
            {
                $result->setState(WorkflowPluginResult::STATE_ERROR);
                $result->setMessage('An error occured while deleting item from frontend');

                $this->logError(sprintf(
                    "An error occured while deleting item with id: %s\n
                    The client api call threw the following error:\n%s",
                    $workflowItem->getIdentifier(),
                    $e->getMessage()
                ));
            }
        }
        else
        {
            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setMessage('Skipping frontend deletion due to system settings...');

            $this->logInfo(sprintf(
                "Skipped frontend sync for deletion  of item: %s due to system settings",
                $workflowItem->getIdentifier()
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
        return TRUE;
    }
}

?>
