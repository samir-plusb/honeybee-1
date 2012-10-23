<?php

/**
 * The Api_DeduplicateAction is repsonseable handling dedup api requests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Api_DeduplicateAction extends ShofiBaseAction
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
        $workflowService = ShofiWorkflowService::getInstance();
        $itemId = $parameters->getParameter('item_id');
        $item = $workflowService->fetchWorkflowItemById($itemId);
        $item->setAttribute('no_duplicate', TRUE);
        $workflowService->storeWorkflowItem($item);
        // @todo If the group-leader is one of the duplicates, we'll have to determine a new group leader,
        // or if only one item is left remove the group.

        return 'Success';
    }
}
