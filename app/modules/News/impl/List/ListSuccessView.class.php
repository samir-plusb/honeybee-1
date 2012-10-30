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
        $this->setAttribute('state', $listState);
        $this->getLayer('content')->setSlot(
            'pagination',
            $this->createSlotContainer('Common', 'Paginate', array(
                'config' => $listConfig,
                'state' => $listState
            ))
        );

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $listState = $this->getAttribute('state');
        $page = round($listState->getOffset() / $listState->getLimit()) + 1;

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'midas.breadcrumbs', array());
        if (1 <= count($breadcrumbs))
        {
            array_splice($breadcrumbs, 1);
        }
        $routeParams = array(
            'offset' =>  $listState->getOffset(), 
            'limit' => $listState->getLimit(),
            'sorting' => array(
                'field' => $listState->getSortField(),
                'direction' => $listState->getSortDirection()
            )
        );
        if (! $listState->hasSearch() && ! $listState->hasFilter())
        {
            $breadcrumbs = array(array(
                'text' => 'Übersicht, Seite: ' . $page,
                'link' => $routing->gen('shofi.list', $routeParams),
                'info' => 'Orte - Übersicht, Seite: ' . $page,
                'icon' => 'icon-list'
            ));
        }
        else if ($listState->hasSearch())
        {
            $routeParams['search_phrase'] = $listState->getSearch();
            $breadcrumbs[] = array(
                'text' => sprintf('Suche nach "%s"', $listState->getSearch()),
                'link' => $routing->gen('shofi.list', $routeParams),
                'info' => sprintf('Suchergebnis für "%s", Seite: %s', $listState->getSearch(), $page),
                'icon' => 'icon-search'
            );
        }
        else if ($listState->hasFilter())
        {
            $routeParams['filter'] = $listState->getFilter();
            $breadcrumbs[] = array(
                'text' => 'Erweiterte Suche',
                'link' => $routing->gen('shofi.list', $routeParams),
                'info' => 'Suchergebnis für erweiterte, Seite: ' . $page,
                'icon' => 'icon-search'
            );
        }
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }
}
