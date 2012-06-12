<?php

class QueryBuilder implements IQueryBuilder
{
    protected $listConfig;

    public function __construct(IListConfig $listConfig)
    {
        $this->listConfig = $listConfig;
    }

    public function build(IListState $listState, $filterDeleted = TRUE)
    {
        $query = $this->createQuery($listState);
        if ($listState->hasLimit())
        {
            $query->setLimit($listState->getLimit());
        }
        if ($listState->hasOffset())
        {
            $query->setFrom($listState->getOffset());
        }
        if (($sorting = $this->prepareSortingParams(
            $listState->getSortField(), 
            $listState->getSortDirection()
        )))
        {
            $query->addSort($sorting);
        }

		if (TRUE === $filterDeleted)
		{
        	$query->setFilter(new Elastica_Filter_Not(
            	new Elastica_Filter_Term(
                	array('attributes.marked_deleted' => TRUE)
            	)
        	));
		}
        return $query;
    }

    protected function createQuery(IListState $listState)
    {
        $query = NULL;
        if ($listState->hasSearch())
        {
            if (IListState::MODE_SUGGEST === $listState->getSearchMode())
            {
                $query = $this->createSuggestQuery($listState->getSearch());
            }
            else
            {
                $query = $this->createSearchQuery($listState->getSearch());
            }
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
        $terms = array();
        foreach (explode(' ', $search) as $term)
        {
            $terms[] = trim($term);
        }
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
            $textQuery->setFieldQuery('_all', implode(' ', $terms));
            $textQuery->setFieldType('_all', 'phrase_prefix');
            $textQuery->setFieldMaxExpansions('_all', 15);
            $query->setQuery($textQuery);
        }
        return $query;
    }

    protected function createSuggestQuery($search)
    {
        $terms = array();
        foreach (explode(' ', $search) as $term)
        {
            $terms[] = trim($term);
        }
        $suggestField = $this->listConfig->getSuggestField();
        $suggestQuery = new Elastica_Query_Text();
        $suggestQuery->setFieldQuery($suggestField, $search);
        $suggestQuery->setFieldType($suggestField, 'phrase_prefix');
        $suggestQuery->setFieldMaxExpansions($suggestField, 15);
        return Elastica_Query::create($suggestQuery);
    }

    protected function createFilterQuery(array $filter)
    {
        $query = new Elastica_Query();
        $filterContainer = new Elastica_Query_Bool();
        $filters = array();
        foreach ($filter as $fieldname => $fieldvalue)
        {
            if (!empty($fieldvalue))
            {
                $filterQuery = new Elastica_Query_Text();
                $filterQuery->setFieldQuery($fieldname, $fieldvalue);
                $filterQuery->setFieldType($fieldname, 'phrase_prefix');
                $filterQuery->setFieldMaxExpansions($fieldname, 15);
                $filters[] = $filterQuery;
            }
        }
        if (1 == count($filters))
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

    protected function prepareSortingParams($sortField = NULL, $sortDirection = NULL)
    {
        if (! $sortField)
        {
            return array(
                array('_uid' => IListState::SORT_ASC)
            );
        }
        if (! $this->listConfig->hasField($sortField))
        {
            throw new Exception(
                "The given sortfield '$sortField' does not exist within the currently loaded config."
            );
        }
        $listField = $this->listConfig->getField($sortField);
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
