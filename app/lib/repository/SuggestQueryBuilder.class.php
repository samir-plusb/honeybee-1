<?php

class SuggestQueryBuilder implements IQueryBuilder
{
    public function build(array $specification)
    {
        $term = $specification['term'];
        $field = $specification['field'];
        $sortSpec = $specification['sorting'];

        $suggestQuery = new Elastica_Query_Text();
        $suggestQuery->setFieldQuery($field, $term);
        $suggestQuery->setFieldType($field, 'phrase_prefix');
        $suggestQuery->setFieldMaxExpansions($field, 15);

        $query = Elastica_Query::create($suggestQuery);

        $sortDefs = array();
        if (! empty($sortSpec))
        {   
            $sortDefs[] = $sortSpec;
        }
        $sortDefs[] = array('_uid' => 'asc');

        return $query->addSort($sortDefs);
    }
}
