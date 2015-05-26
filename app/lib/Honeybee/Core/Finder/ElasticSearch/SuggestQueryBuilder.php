<?php

namespace Honeybee\Core\Finder\ElasticSearch;

use Honeybee\Core\Finder\IQueryBuilder;
use Elastica;

class SuggestQueryBuilder implements IQueryBuilder
{
    /**
     * @todo consider sortDirection
     */
    public function build(array $specification, $sortDirection = 'asc')
    {
        $term = isset($specification['term']) ? $specification['term'] : '';
        $field = $specification['field'];
        $sortSpec = $specification['sorting'];

        $query = null;
        if (!empty($term)) {
            $suggestQuery = new Elastica\Query\Match();
            $suggestQuery->setFieldQuery($field . '.suggest', $term);
            $suggestQuery->setFieldType($field . '.suggest', 'phrase_prefix');
            $suggestQuery->setFieldMaxExpansions($field . '.suggest', 15);

            $query = Elastica\Query::create($suggestQuery);

        } else {
            $query = Elastica\Query::create(null);
        }

        $sortDefs = array();
        if (! empty($sortSpec))
        {
            $sortDefs[] = $sortSpec;
        }
        $sortKey = sprintf('%s.sort', $field);
        $sortDefs[$sortKey] = 'asc';

        return $query->addSort($sortDefs);
    }
}
