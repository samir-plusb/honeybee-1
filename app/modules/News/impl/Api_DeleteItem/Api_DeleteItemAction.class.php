<?php

/**
 * The News_Api_DeleteItemAction is repsonseable handling the deletion of content items from the editing gui.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_DeleteItemAction extends NewsBaseAction
{

    /**
     * Execute writes for delete item action, hence delete the given content item.
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
        $workflowItem = $ticket->getItem();
        if (($contentItem = $workflowItem->getContentItem($contentItemId)))
        {
            try
            {
                $workflowItem->removeContentItem($contentItem);
                $supervisor = WorkflowSupervisor::getInstance();
                $supervisor->getWorkflowItemStore()->save($workflowItem);
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

    /**
     * Handle write errors, hence failed validation on the incoming data.
     *
     * @param AgaviRequestDataHolder $parameters
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setAttribute(
            'err_message',
            'Validation error occured. Please verify the data you are sending'
        );
        return 'Error';
    }
}

?>
