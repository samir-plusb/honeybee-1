<?php

/**
 * The News_List_ListSuccessView class handles News/List success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_List_ListSuccessView extends NewsBaseView
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
        $ticketStore = WorkflowSupervisorFactory::createByTypeKey('news')->getWorkflowTicketStore();
        foreach ($this->getAttribute('items', array()) as $workflowItem)
        {
            $workflowItemData = $workflowItem->toArray();
            $ticket = $ticketStore->fetchByIdentifier($workflowItemData['ticketId']);
            $workflowItemData['ticket'] = array(
                'id' => $ticket->getIdentifier(),
                'rev' => $ticket->getRevision()
            );
            $workflowItemData['owner'] = $ticket->getCurrentOwner();
            $listData[] = $workflowItemData;
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
        $msg = "News/List/Success@Text" . PHP_EOL;
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
        $ticketStore = WorkflowSupervisorFactory::createByTypeKey('news')->getWorkflowTicketStore();
        foreach ($this->getAttribute('items', array()) as $item)
        {
            $itemData = $item->toArray();
            $ticket = $ticketStore->fetchByIdentifier($itemData['ticketId']);
            $itemData['ticket'] = array(
                'id' => $ticket->getIdentifier(),
                'rev' => $ticket->getRevision()
            );
            $itemData['masterRecord']['content'] = strip_tags(
                htmlspecialchars_decode($itemData['masterRecord']['content'])
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

    public function setupHtml(AgaviRequestDataHolder $parameters, $layoutName = NULL)
    {
        parent::setupHtml($parameters, $layoutName);

        $this->setAttribute('_title', 'Midas - News Stream');

        $listConfig = ListConfig::fromArray(AgaviConfig::get('news.list_config', array()));
        $listState = ListState::fromArray(array(
            'offset' => $parameters->getParameter('offset', 0),
            'limit' => $parameters->getParameter('limit', $listConfig->getDefaultLimit()),
            'sortDirection' => $parameters->getParameter('sorting[direction]', 'masterRecord.created.date'),
            'sortField' => $parameters->getParameter('sorting[field]', 'asc'),
            'data' => $this->getAttribute('items'),
            'totalCount' => $this->getAttribute('totalCount'),
            'search' => $parameters->getParameter('search_phrase')
        ));

        $this->getLayer('content')->setSlot(
            'pagination',
            $this->createSlotContainer('Common', 'Paginate', array(
                'config' => $listConfig,
                'state' => $listState
            ))
        );
    }

}

?>