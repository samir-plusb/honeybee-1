<?php

class TicketFinderModel extends AgaviModel implements AgaviISingletonModel
{
    const DEFAULT_LIMIT = 50;

    /**
     *
     * @var Elastica_Client
     */
    protected $elasticClient;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);
        $this->elasticClient = new Elastica_Client(array(
            'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
            'port'      => AgaviConfig::get('elasticsearch.port', 9200),
            'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
        ));
    }
    
    public function fetchAll($offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        $query = new Elastica_Query(
            new Elastica_Query_MatchAll()
        );
        $query->setLimit($limit)->setFrom($offset);

        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('ticket');
        
        return $this->hydrateResult(
            $type->search($query)
        );
    }
    
    public function search($searchPhrase, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        $textQuery = new Elastica_Query_Text();
        $textQuery->setField('_all', $searchPhrase);
        
        $boolQuery = new Elastica_Query_Bool();
        $boolQuery->addMust($textQuery);
        
        $childQuery = new Elastica_Query_HasChild($boolQuery, 'item');
        
        $query = new Elastica_Query($childQuery);
        $query->setLimit($limit)->setFrom($offset);
        
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('ticket');
        
        return $this->hydrateResult(
            $type->search($query)
        );
    }
    
    protected function hydrateResult(Elastica_ResultSet $result)
    {
        $itemIds = array();
        $tickets = array();
        /* @var $items Elastica_Result */
        foreach($result->getResults() as $doc)
        {
            $data = $doc->getData();
            $itemId = $data['item'];
            $itemIds[] = $itemId;
            unset($data['item']);
            $tickets[$itemId] = new WorkflowTicket($data);
        }
        return $this->loadItemsIntoTickets($tickets, $itemIds);
    }
    
    protected function loadItemsIntoTickets(array $tickets, array $itemIds)
    {
        if (empty($tickets))
        {
            return array();
        }
        $itemFinder = $this->getContext()->getModel('ItemFinder');
        foreach ($itemFinder->findByIds($itemIds) as $item)
        {
            $identifier = $item->getIdentifier();
            if (! isset($tickets[$identifier]))
            {
                throw new WorkflowException(
                    "Integrity constraint violation: No ticket given for item: " . $identifier
                );
            }
            $tickets[$identifier]->setWorkflowItem($item);
        }
        return array_values($tickets);
    }
}
