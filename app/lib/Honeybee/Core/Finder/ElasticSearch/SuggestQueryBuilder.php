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
        $term = $specification['term'];
        $field = $specification['field'];
        $sortSpec = $specification['sorting'];

        $suggestQuery = new Elastica\Query\Text();
        $suggestQuery->setFieldQuery($field . '.suggest', $term);
        $suggestQuery->setFieldType($field . '.suggest', 'phrase_prefix');
        $suggestQuery->setFieldMaxExpansions($field . '.suggest', 15);

        $query = Elastica\Query::create($suggestQuery);

        $sortDefs = array();
        if (! empty($sortSpec))
        {   
            $sortDefs[] = $sortSpec;
        }
        $sortDefs[] = array('_uid' => 'asc');

        return $query->addSort($sortDefs);
    }
}
