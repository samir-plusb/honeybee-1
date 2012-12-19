<?php

class ElasticaQueryBuilder implements IQueryBuilder
{
    public function build(array $specification)
    {
        $state = $specification['state'];
        $config = $specification['config'];

        $innerQuery = $state->hasSearch() 
            ? $this->buildSearchQuery($state->getSearch())
            : new Elastica_Query_MatchAll();

        $filter = NULL;
        if($state->hasFilter())
        {
            $filter = $this->buildFilter(
                $state->getFilter()
            );
        }

        $query = Elastica_Query::create($innerQuery);

        $query->addSort(
            $this->prepareSortingParams($config, $state)
        );

        // @todo add filter for deleted documents here?

        if ($filter)
        {
            $query->setFilter($filter);
        }

        return $query;
    }

    protected function buildSearchQuery($search)
    {
        $query = NULL;

        // @todo add "search syntax sugar" and parse it here.

        $query = new Elastica_Query_Text();
        $query->setFieldQuery('_all', $search);

        return $query;
    }

    protected function buildFilter(array $filters)
    {
        $filter = NULL;

        if (1 === count($filters))
        {
            $filter = new Elastica_Filter_Term($filters);
        }
        else if (1 < count($filters))
        {
            $filter = new Elastic_Filter_And();

            foreach ($filters as $fieldname => $fieldvalue)
            {
                if (! empty($fieldvalue))
                {
                    $filter->add(
                        new Elastica_Filter_Term(array(
                            $fieldname => $fieldvalue
                        )
                    ));
                }
            }
        }
        else
        {
            throw new Exception(
                "You must supply at least one filter to the buildFilter method."
            );
        }

        return $filter;
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
