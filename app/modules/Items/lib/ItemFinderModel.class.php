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

    protected $currentItemId = NULL;

    public function enableEditFilter($curItemId)
    {
        $this->currentItemId = $curItemId;
    }

    public function disableEditFilter()
    {
        $this->currentItemId = NULL;
    }

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

        $filterContainer = $this->addNewItemsFilter(
            new Elastica_Filter_Not(
                new Elastica_Filter_Term(
                    array('currentState.step' => 'delete_news')
                )
            )
        );
        $query->setLimit($limit)->setFrom($offset)->addSort(
            array(
                array(self::$sortMapping[$sortField] => $sortDirection),
                array('_uid' => 'asc')
            )
        );
        $query->setFilter($filterContainer);
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

        $filterContainer = $this->addNewItemsFilter(
            new Elastica_Filter_Not(
                new Elastica_Filter_Term(
                    array('currentState.step' => 'delete_news')
                )
            )
        );
        $query->setFilter($filterContainer);
        $query->setLimit($limit)->setFrom($offset)->addSort(
            array(
                array(self::$sortMapping[$sortField] => $sortDirection),
                array('_uid' => 'asc')
            )
        );
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('item');

        return $this->hydrateResult(
            $type->search($query)
        );
    }

    public function nearBy(array $where, $sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        if (! isset($where['dist']) || ! isset($where['lon']) || ! isset($where['lat']))
        {
            throw new InvalidArgumentException(
                "Missing information on where you would like to search the nearby items." . PHP_EOL .
                "Be sure to pass dist, lon and lat inside the \$where array."
            );
        }

        if (! isset(self::$sortMapping[$sortField]))
        {
            throw new Exception("Invalid sort field given. The field " . $sortField . " is not supported.");
        }

        $filterContainer = new Elastica_Filter_And();
        $filterContainer->addFilter(new Elastica_Filter_GeoDistance(
            'contentItems.location.coordinates',
            $where['lat'],
            $where['lon'],
            $where['dist']
        ))->addFilter(
            new Elastica_Filter_Not(new Elastica_Filter_Term(
                array('currentState.step' => 'delete_news')
            ))
        );

        $query = new Elastica_Query(
            new Elastica_Query_Term(array(
                'currentState.workflow' => 'news'
            ))
        );
        $query->setFilter($filterContainer)->setLimit($limit)->setFrom($offset)->addSort(
            array(
                array(self::$sortMapping[$sortField] => $sortDirection),
                array('_uid' => 'asc')
            )
        );
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('item');

        return $this->hydrateResult(
            $type->search($query)
        );
    }

    protected function addNewItemsFilter(Elastica_Filter_Abstract $filter)
    {
        if (NULL === $this->currentItemId)
        {
            return $filter;
        }

        $filterContainer = $filter;
        if (! ($filterContainer instanceof Elastica_Filter_And))
        {
            $filterContainer = new Elastica_Filter_And();
        }
        $filterContainer->addFilter(
            new Elastica_Filter_Term(
                array('currentState.step' => 'refine_news')
        ))->addFilter(
            new Elastica_Filter_Term(
                array('currentState.owner' => 'nobody')
        ));

        $idOrFilter = new Elastica_Filter_Or();
        return $idOrFilter->addFilter(
            new Elastica_Filter_Ids('item', array($this->currentItemId))
        )->addFilter($filterContainer);
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
