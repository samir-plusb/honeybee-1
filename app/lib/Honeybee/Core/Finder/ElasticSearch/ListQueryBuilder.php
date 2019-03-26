<?php

namespace Honeybee\Core\Finder\ElasticSearch;

use Honeybee\Core\Finder\IQueryBuilder;
use IListConfig;
use IListState;

use Elastica;

class ListQueryBuilder extends DefaultQueryBuilder
{
    public function build(array $specification)
    {
        $state = $specification['state'];
        $config = $specification['config'];

        $innerQuery = $state->hasSearch()
            ? $this->buildSearchQuery($state->getSearch())
            : new Elastica\Query\MatchAll();

        $filter = new Elastica\Filter\BoolNot(
            new Elastica\Filter\Term(array('meta.is_deleted' => TRUE))
        );

        if ($state->hasFilter())
        {
            $container = new Elastica\Filter\BoolAnd();
            $container->addFilter($filter);
            $container->addFilter(
                $this->buildFilter($state->getFilter())
            );
            $filter = $container;
        }

        $filteredQuery = new Elastica\Query\Filtered($innerQuery, $filter);
        $query = Elastica\Query::create($filteredQuery)->addSort(
            $this->prepareSortingParams($config, $state)
        );

        return $query;
    }

    protected function prepareSortingParams(IListConfig $config, IListState $state)
    {
        $sortDirection = $state->getSortDirection();
        $sortField = $state->getSortField();

        if (! $sortField)
        {
            return array('shortId' => IListState::SORT_DESC);
        }

        if (! $config->hasField($sortField))
        {
            throw new \Exception("The given sortfield '$sortField' does not exist within the currently loaded config.");
        }

        $listField = $config->getField($sortField);
        if (! $listField->hasSortfield())
        {
            throw new \Exception(
                "The given sortfield '$sortField' does not support sorting." . PHP_EOL .
                " Make sure to add a 'sortfield' param to your corresponding listconfg."
            );
        }

        $esSortFieldName = $listField->getSortfield();

        return array(
            $esSortFieldName => $sortDirection,
            'shortId' => IListState::SORT_DESC
        );
    }
}