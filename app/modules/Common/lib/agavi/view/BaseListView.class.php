<?php

abstract class BaseListView extends ProjectbaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);

        $listData = array();
        $ticketStore = WorkflowSupervisorFactory::createByTypeKey('shofi')->getWorkflowTicketStore();
        /* @var $workflowItem IWorkflowItem */
        foreach ($this->getAttribute('items', array()) as $workflowItem)
        {
            $workflowItemData = $workflowItem->toArray();
            // @todo This findOne query is a potential bottle neck and does not scale!
            // Better: Collect all tickets id's and fetch the data in one query.
            $ticket = $ticketStore->fetchByIdentifier(
                $workflowItem->getTicketId()
            );
            $ticketData = array(
                'id' => $ticket->getIdentifier(),
                'rev' => $ticket->getRevision()
            );
            $listData[] = array(
                'data' => $workflowItemData,
                'ticket' => $ticketData
            );
        }
        $this->setAttribute('user', $this->getContext()->getUser()->getAttribute('login'));
        $this->setAttribute('listData', $listData);

        $this->createListSlot($parameters, $listData);
    }

    public function setupHtml(AgaviRequestDataHolder $parameters, $layoutName = NULL)
    {
        parent::setupHtml($parameters, $layoutName);
        $this->setAttribute('_title', 'Honeybee 3.0');
    }

    protected function createListSlot(AgaviRequestDataHolder $parameters, array $listData)
    {
        $listParams = array(
            'paging_range' => AgaviConfig::get('news.pagination.range', 9),
            'total_count'  => $this->getAttribute('totalCount'),
            'offset'       => $parameters->getParameter('offset', 0),
            'limit'        => $parameters->getParameter('limit', AgaviConfig::get('pagination.default_limit', 15)),
            'sorting'      => $this->getAttribute('sorting'),
            'list_route'   => 'shofi.list',
            'list_data'    => $listData,
            'translation_domain' => 'shofi.list',
            'list_fields'  => array(
                'masterRecord.title',
                'masterRecord.created.date',
                'masterRecord.source',
                'currentState.step',
                'masterRecord.category'
            )
        );

        if ($this->hasAttribute('search'))
        {
            $listParams['search'] = $this->getAttribute('search');
        }

        $this->getLayer('content')->setSlot(
            'list',
            $this->createSlotContainer('Common', 'List', $listParams)
        );
    }
}

?>
