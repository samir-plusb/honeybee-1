<?php

class ItemFinderModel extends AgaviModel implements AgaviISingletonModel
{
    const DEFAULT_LIMIT = 50;

    const SORT_DESC = 'desc';

    const SORT_ASC = 'asc';

    const SORT_SUFFIX = '_sortable';

    private static $sortMapping = array(
        'title'        => 'importItem.title.title_sortable',
        'source'       => 'importItem.source.source_sortable',
        'timestamp'    => 'importItem.created.date',
        'state'        => 'currentState.step',
        'category'     => 'importItem.category.category_sortable',
        'owner'        => 'currentState.owner',
        'priority'     => 'contentItems.priority'
    );

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

    public function fetchAll($sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        if (! isset(self::$sortMapping[$sortField]))
        {
            throw new Exception("Invalid sort field given. The field " . $sortField . " is not supported.");
        }
        $query = new Elastica_Query(
            new Elastica_Query_Term(
                array('currentState.workflow' => 'news')
            )
        );
        $query->setLimit($limit)->setFrom($offset)->setSort(
            array(self::$sortMapping[$sortField] => $sortDirection)
        );
        $query->setFilter(new Elastica_Filter_Not(new Elastica_Filter_Term(
            array('currentState.step' => 'delete_news')
        )));
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('item');

        return $this->hydrateResult(
            $type->search($query)
        );
    }

    public function search($searchPhrase, $sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        if (! isset(self::$sortMapping[$sortField]))
        {
            throw new Exception("Invalid sort field given. The field " . $sortField . " is not supported.");
        }

        $terms = explode(' ', $searchPhrase);
        $query = new Elastica_Query();
        if (1 === count($terms))
        {
            $query->setQuery(
                new Elastica_Query_Wildcard('_all', $searchPhrase)
            );
        }
        else
        {
            $termQuery = new Elastica_Query_Terms('_all', $terms);
            $termQuery->setMinimumMatch(count($terms));
            $query->setQuery($termQuery);
        }
        $query->setLimit($limit)->setFrom($offset)->setSort(
            array(self::$sortMapping[$sortField] => $sortDirection)
        );
        $query->setFilter(new Elastica_Filter_Not(new Elastica_Filter_Term(
            array('currentState.step' => 'delete_news')
        )));
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('item');

        return $this->hydrateResult(
            $type->search($query)
        );
    }

    public function filter(array $filter, $sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {

    }

    protected function hydrateResult(Elastica_ResultSet $result)
    {
        $items = array();
        /* @var $items Elastica_Result */
        foreach($result->getResults() as $doc)
        {
            $items[] = new WorkflowItem($doc->getData());
        }
        return array(
            'items'      => $items,
            'totalCount' => $result->getTotalHits()
        );
    }
}

?>
