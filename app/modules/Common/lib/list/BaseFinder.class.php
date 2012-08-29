<?php

abstract class BaseFinder implements IFinder
{
    const ES_TYPE_NAME = 'item';

    protected $esIndex;

    protected $workflowService;

    protected $listConfig;

	private $filterDeleted = TRUE;

    /**
     * @return string The type to use when searching our index.
     */
    abstract protected function getIndexType();

    public function __construct(Elastica_Index $elasticSearchIndex, IListConfig $listConfig, IWorkflowService $workflowService)
    {
    	$this->listConfig = $listConfig;
        $this->esIndex = $elasticSearchIndex;
        $this->workflowService = $workflowService;
    }

    public function query(Elastica_Query_Abstract $query = NULL, Elastica_Filter_Abstract $filter = NULL, $offset = 0, $limit = 1000)
    {
        $query = Elastica_Query::create($query);
        $query->setLimit($limit);
        $query->setFrom(0);
        if ($filter)
        {
            $query->setFilter($filter);
        }
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        return $this->hydrateResult(
            $esType->search($query)
        );
    }

    public function findByIds(array $ids)
    {
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        /*
        return $this->hydrateDocuments(
            $esType->getDocuments($ids)
        );
        */
        $query = Elastica_Query::create(NULL);
        $filter = new Elastica_Filter_Ids($this->getIndexType(), $ids);
        $query->setFilter($filter);
        $query->setLimit(1000);
        return $this->hydrateResult(
            $esType->search($query)
        );
    }
	
	public function ignoreDeletedItems($ignore = TRUE)
	{
		$this->filterDeleted = $ignore;
	}

    public function getWorkflowService()
    {
    	return $this->workflowService;
    }

    public function getFieldFacet($fieldname, $listState = NULL)
    {
        $this->esIndex->refresh();
        $facetname = $fieldname.'-facet';
        $query = NULL;
        if (NULL === $listState)
        {
            $query = Elastica_Query::create(NULL);
        }
        else
        {
            $queryBuilder = new QueryBuilder($this->listConfig);
            $query = $queryBuilder->build($listState, $this->filterDeleted);
        }
        $facet = new Elastica_Facet_Terms($facetname);
        $facet->setField($fieldname);
        $query->addFacet($facet);
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        $resultData = $esType->search($query);
        $facets = $resultData->getFacets();
        $facetData = $facets[$facetname];
        return FinderResult::fromArray(array(
            'items' => ! $facetData['terms'] ? array() : $facetData['terms'],
            'totalCount' => $facetData['total']
        ));
    }

    public function find(IListState $listState)
    {
        $queryBuilder = new QueryBuilder($this->listConfig);
        
        return $this->fireQuery(
            $queryBuilder->build($listState, $this->filterDeleted)
        );
    }

    public function count(IListState $listState)
    {
        $queryBuilder = new QueryBuilder($this->listConfig);
        
        return $this->countQuery(
            $queryBuilder->build($listState)
        );
    }

    protected function countQuery(Elastica_Query $query)
    {
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        return $esType->search($query)->count();
    }

    protected function fireQuery(Elastica_Query $query)
    {
        $this->esIndex->refresh();

        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        $resultData = $esType->search($query);
        return $this->hydrateResult($resultData);
    }

    protected function hydrateResult(Elastica_ResultSet $result)
    {
        $items = array();
        /* @var $resultDoc Elastica_Result */
        foreach($result->getResults() as $resultDoc)
        {
            $items[] = $this->workflowService->createWorkflowItem($resultDoc->getData());
        }
        return FinderResult::fromArray(array(
            'items' => $items,
            'totalCount' => $result->getTotalHits()
        ));
    }

    protected function hydrateDocuments(array $documents)
    {
        $items = array();
        /* @var $resultDoc Elastica_Result */
        foreach($documents as $resultDoc)
        {
            $items[] = $this->workflowService->createWorkflowItem($resultDoc->getData());
        }
        return FinderResult::fromArray(array(
            'items' => $items,
            'totalCount' => count($items)
        ));
    }
}

?>
