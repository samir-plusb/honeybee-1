<?php

/**
 * The Items_List_ListSuccessView class handles Items/List success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_List_ListSuccessView extends ItemsBaseView
{
    /**
     * Handle presentation logic for the web (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);
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
            $itemData['owner'] = $ticket->getCurrentOwner();
            $listData[] = $itemData;
        }
        $this->setAttribute('user', $this->getContext()->getUser()->getAttribute('login'));
        $this->setAttribute('listData', $listData);
    }

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "Items/List/Success@Text" . PHP_EOL;
        $msg .= print_r($this->getAttribute('items'), TRUE);
        $this->getResponse()->setContent($msg);
    }

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
            $itemData['importItem']['content'] = strip_tags(htmlspecialchars_decode($itemData['importItem']['content']));
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

    public function setupHtml(AgaviRequestDataHolder $parameters, $layoutName = NULL)
    {
        parent::setupHtml($parameters, $layoutName);
        $this->setAttribute('_title', 'Midas - News Stream');

        $paginationData = array(
            'paging_range' => AgaviConfig::get('items.pagination.range', 9),
            'total_count'  => $this->getAttribute('totalCount'),
            'offset'       => $parameters->getParameter('offset', 0),
            'limit'        => $parameters->getParameter('limit', AgaviConfig::get('pagination.default_limit', 15)),
            'sorting'      => $this->getAttribute('sorting')
        );

        if ($this->hasAttribute('search_phrase'))
        {
            $paginationData['search_phrase'] = $this->getAttribute('search_phrase');
        }

        $this->getLayer('content')->setSlot(
            'pagination',
            $this->createSlotContainer('Items', 'Paginate', $paginationData)
        );
    }

}

?>