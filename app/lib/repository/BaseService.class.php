<?php

abstract class BaseService implements IService
{
    private $module;

    private $queryBuilder;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function initialize($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function fetchListData(IListConfig $config, IListState $state)
    {
        $query = $this->buildListQuery($config, $state);
        $limit = $state->hasLimit() ? $state->getLimit() : 0;
        $offset = $state->hasOffset() ? $state->getOffset() : 0;
        
        return $this->module->getRepository()->find($query, $limit, $offset);
    }

    public function buildListQuery(IListConfig $config, IListState $state)
    {
        $query = $this->createQuery($state);

        $sorting = $this->prepareSortingParams($config, $state);

        if ($sorting)
        {
            $query->addSort($sorting);
        }

        // add filter for deleted documents.

        return $query;
    }

    protected function createQuery(IListState $listState)
    {
        $query = NULL;

        if ($listState->hasSearch())
        {
            $query = $this->createSearchQuery($listState->getSearch());
        }
        else if($listState->hasFilter())
        {
            $query = $this->createFilterQuery($listState->getFilter());
        }
        else
        {
            $query = new Elastica_Query(new Elastica_Query_MatchAll());
        }

        return $query;
    }

    protected function createSearchQuery($search)
    {
        $query = new Elastica_Query();

        if (FALSE !== strpos($search, '*'))
        {
            $query->setQuery(
                new Elastica_Query_Wildcard('_all', $search)
            );
        }
        else
        {
            $textQuery = new Elastica_Query_Text();
            $textQuery->setFieldQuery('_all', $search);
            $query->setQuery($textQuery);
        }

        return $query;
    }

    protected function createFilterQuery(array $filter)
    {
        $query = new Elastica_Query();
        $filterContainer = new Elastica_Query_Bool();

        $filters = array();
        foreach ($filter as $fieldname => $fieldvalue)
        {
            if (! empty($fieldvalue))
            {
                $filterQuery = new Elastica_Query_Text();
                $filterQuery->setFieldQuery($fieldname, $fieldvalue);
                $filterQuery->setFieldType($fieldname, 'phrase_prefix');
                $filterQuery->setFieldMaxExpansions($fieldname, 15);
                $filters[] = $filterQuery;
            }
        }

        if (1 === count($filters))
        {
            $query->setQuery($filters[0]);
        }
        else if(1 < count($filters))
        {
            foreach ($filters as $filter)
            {
                $filterContainer->addShould($filter);
            }
            $query->setQuery($filterContainer);
        }

        return $query;
    }

    protected function prepareSortingParams(IListConfig $config, IListState $state)
    {
        $sortDirection = $state->getSortDirection();
        $sortField = $state->getSortField();

        if (! $sortField)
        {
            return array(
                array('_uid' => IListState::SORT_ASC)
            );
        }

        if (! $config->hasField($sortField))
        {
            throw new Exception(
                "The given sortfield '$sortField' does not exist within the currently loaded config."
            );
        }

        $listField = $config->getField($sortField);
        if (! $listField->hasSortfield())
        {
            throw new Exception(
                "The given sortfield '$sortField' does not support sorting." . PHP_EOL .
                " Make sure to add a 'sortfield' param to your corresponding listconfg."
            );
        }

        $esSortFieldName = $listField->getSortfield();

        return array(
            array($esSortFieldName => $sortDirection),
            array('_uid' => IListState::SORT_ASC)
        );
    }
}
