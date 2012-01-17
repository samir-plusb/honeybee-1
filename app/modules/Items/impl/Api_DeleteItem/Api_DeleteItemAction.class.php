<?php

/**
 * The Items_Api_DeleteItemAction is repsonseable handling the deletion of content items from the editing gui.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_DeleteItemAction extends ItemsBaseAction
{

    /**
     * Execute the read logic for this action, hence extract the data.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $ticket = $parameters->getParameter('ticket');
        $contentItemId = $parameters->getParameter('content_item');
        $workflowItem = $ticket->getWorkflowItem();
        if (($contentItem = $workflowItem->getContentItem($contentItemId)))
        {
            try
            {
                $workflowItem->removeContentItem($contentItem);
                $supervisor = Workflow_SupervisorModel::getInstance();
                $supervisor->getItemPeer()->storeItem($workflowItem);
            }
            catch (Exception $e)
            {
                $this->setAttribute(
                    'err_message',
                    'Unexpected error while deleting item: ' . PHP_EOL . $e->getMessage()
                );
                return 'Error';
            }
        }
        return 'Success';
    }

    public function handleWriteError(AgaviRequestDataHolder $parameters)
    {
        $this->setAttribute(
            'err_message',
            'Validation error occured. Please verify the data you are sending'
        );
        return 'Error';
    }
}

?>
