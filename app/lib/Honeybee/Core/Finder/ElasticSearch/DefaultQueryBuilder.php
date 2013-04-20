<?php

namespace Honeybee\Core\Finder\ElasticSearch;

use Honeybee\Core\Finder\IQueryBuilder;
use IListConfig;
use IListState;

use Elastica;

class DefaultQueryBuilder implements IQueryBuilder
{
    public function build(array $specification)
    {
        $search = isset($specification['search']) ? $specification['search'] : NULL;
        $filterFields = isset($specification['filter']) ? $specification['filter'] : NULL;
        $sort = isset($specification['sort']) ? $specification['sort'] : NULL;

        $innerQuery = $search
            ? $this->buildSearchQuery($search)
            : new Elastica\Query\MatchAll();

        $query = NULL;
        if ($search)
        {
            $query = Elastica\Query::create(
                $this->buildSearchQuery($search)
            );
        }
        else
        {
            $query = Elastica\Query::create(NULL);
        }

        if (! $sort)
        {
            $sort = array(array('shortId' => 'desc'));
        }

        $query->addSort($sort);

        if($filterFields)
        {
            $filter = $this->buildFilter($filterFields);
            $container = new Elastica\Filter\BoolAnd();
            $container->addFilter($filter);
            $container->addFilter(
                new Elastica\Filter\BoolNot(new Elastica\Filter\Term(
                    array('meta.is_deleted' => TRUE)
                ))
            );
            $query->setFilter($container);
        }
        else
        {
            $query->setFilter(
                new Elastica\Filter\BoolNot(new Elastica\Filter\Term(
                    array('meta.is_deleted' => TRUE)
                ))
            );
        }

        return $query;
    }

    protected function buildSearchQuery($search)
    {
        $query = NULL;

        // @todo add "search syntax sugar" and parse it here.

        $query = new Elastica\Query\Match();
        $query->setFieldQuery('_all', $search);
        $query->setFieldType('_all', 'phrase_prefix');

        return $query;
    }

    protected function buildFilter(array $filters)
    {
        $filter = NULL;

        if (1 === count($filters))
        {
            $fields = array_keys($filters);
            $field = $fields[0];
            $filter = new Elastica\Filter\Term($filters);
        }
        else if (1 < count($filters))
        {
            $filter = new Elastica\Filter\BoolAnd();

            foreach ($filters as $fieldname => $fieldvalue)
            {
                if (! empty($fieldvalue))
                {
                    $filter->addFilter(
                        new Elastica\Filter\Term(array($fieldname => $fieldvalue)
                    ));
                }
            }
        }
        else
        {
            throw new Exception("You must supply at least one filter to the buildFilter method.");
        }

        return $filter;
    }
}
