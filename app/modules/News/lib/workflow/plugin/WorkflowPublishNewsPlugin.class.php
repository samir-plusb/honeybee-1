<?php

/**
 * This plugin takes care of publishing news to the various subscribers.
 *
 * @author tay
 * @version $Id$
 * @package News
 * @subpackage Workflow/Plugin
 */
class WorkflowPublishNewsPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'publish_news';

    const GATE_ARCHIV = 'archiv';

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
        $newsService = NewsWorkflowService::getInstance();
        $workflowItem = $newsService->fetchWorkflowItemById($this->ticket->getItem());
        $now = new DateTime();
        
        foreach ($workflowItem->getContentItems() as $contentItem)
        {
            $publishDate = $contentItem->getPublishDate();
            if (empty($publishDate))
            {
                $contentItem->applyValues(
                    array('publishDate' => $now->format(DATE_ISO8601))
                );
            }
        }

        $supervisor = $newsService->getWorkflowSupervisor();
        $supervisor->getWorkflowItemStore()->save($workflowItem);
        $result = new WorkflowPluginResult();

        if (TRUE === AgaviConfig::get('news.frontend_sync', FALSE))
        {
            try
            {
                $feClient = new FrontendApiClient();
                $feClient->updateWorkflowItem($workflowItem);

                $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
                $result->setMessage('Successfully published item to frontend.');

                $this->logInfo("Successfully published (news)item: " . $workflowItem->getIdentifier());
            }
            catch(FrontendApiClientException $e)
            {
                $result->setState(WorkflowPluginResult::STATE_ERROR);
                $result->setMessage('An error occured while publishing item to frontend');

                $this->logError(sprintf(
                    "An error occured while publishing item with id: %s\n
                    The client api call threw the following error:\n%s",
                    $workflowItem->getIdentifier(),
                    $e->getMessage()
                ));
            }
        }
        else
        {
            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setMessage('Skipping frontend publishing due to system settings...');
            $this->logInfo("Successfully published (news)item: " . $workflowItem->getIdentifier());
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
