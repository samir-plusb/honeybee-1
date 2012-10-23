<?php

/**
 * This plugin takes care of marking shofi categories as deleted.
 *
 * @author tay
 * @version $Id$
 * @package Shofi_Categories
 * @subpackage Workflow/Plugin
 */
class WorkflowDeleteShofiCategoryPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'shofi_category_delete';

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
            $workflowService = ShofiCategoriesWorkflowService::getInstance();
            $workflowItem = $workflowService->fetchWorkflowItemById($this->ticket->getItem());
            $workflowService->deleteWorkflowItem($workflowItem);

            $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $result->setGate(self::GATE_TRASH);
            $this->logInfo(sprintf(
                "Successfully moved (shofi category)item: %s to the trash",
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
                $cmExport->deleteEntity($workflowItem->getIdentifier(), 'category');
            }
        }
        catch(CouchdbClientException $e)
        {
            $result->setState(WorkflowPluginResult::STATE_ERROR);
            $result->setMessage($e->getMessage());

            $this->logError(sprintf(
                "An error occured while deleting shofi category: %s from the database\n
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
