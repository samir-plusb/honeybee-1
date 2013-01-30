<?php

namespace Honeybee\Core\Finder\ElasticSearch;

use Honeybee\Core\Finder\IQueryBuilder;

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

        $suggestQuery = new \Elastica_Query_Text();
        $suggestQuery->setFieldQuery($field, $term);
        $suggestQuery->setFieldType($field, 'phrase_prefix');
        $suggestQuery->setFieldMaxExpansions($field, 15);

        $query = \Elastica_Query::create($suggestQuery);

        $sortDefs = array();
        if (! empty($sortSpec))
        {   
            $sortDefs[] = $sortSpec;
        }
        $sortDefs[] = array('_uid' => 'asc');

        return $query->addSort($sortDefs);
    }
}
