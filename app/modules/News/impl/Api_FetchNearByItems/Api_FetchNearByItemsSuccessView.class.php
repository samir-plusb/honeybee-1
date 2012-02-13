<?php

/**
 * The News_Api_FetchNearByItems_Api_FetchNearByItemsSuccessView class
 * handles Items/FetchNearByItems success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_FetchNearByItems_Api_FetchNearByItemsSuccessView extends NewsBaseView
{
    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $listData = array();
        $ticketPeer = Workflow_SupervisorModel::getInstance()->getTicketPeer();
        foreach ($this->getAttribute('items', array()) as $item)
        {
            $itemData = $item->toArray();
            $ticket = $ticketPeer->getTicketById($itemData['ticketId']);
            $itemData['ticket'] = array(
                'id' => $ticket->getIdentifier(),
                'rev' => $ticket->getRevision()
            );
            $itemData['importItem']['content'] = strip_tags(
                htmlspecialchars_decode($itemData['importItem']['content'])
            );
            $itemData['owner'] = $ticket->getCurrentOwner();
            $listData[] = $itemData;
        }
        $this->getResponse()->setContent(json_encode(
            array(
                'state' => 'ok',
                'data' => $listData
            )
        ));
    }
}

?>
