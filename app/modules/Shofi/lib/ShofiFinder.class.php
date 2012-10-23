<?php

/**
 * The ShofiFinder is responseable for finding shofi-items and provides several methods to do so.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 */
class ShofiFinder extends BaseFinder
{
    const INDEX_TYPE = 'shofi-place'; // @todo rename into: 'shofi-place'

    public static function create(IListConfig $listConfig)
    {
        return new self(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                self::getElasticSearchDatabaseName()
            )->getResource(),
            $listConfig,
            ShofiWorkflowService::getInstance()
        );
    }

    public static function getElasticSearchDatabaseName()
    {
        // @todo introduce a new shofi.* connections setting and put it to the module.xml
        $connections = AgaviConfig::get('shofi.connections');
        return $connections['elasticsearch'];
    }

    public function getWhereCategoryEmptyByCategorySource($categorySource)
    {
        $andContainer = new Elastica_Filter_And();
        $andContainer->addFilter(
            new Elastica_Filter_Missing('detailItem.category')
        )->addFilter(
            new Elastica_Filter_Term(array('masterRecord.categorySource.raw' => $categorySource))
        )->addFilter(
            new Elastica_Filter_Not(
                new Elastica_Filter_Term(array('attributes.marked_deleted' => TRUE))
            )
        );

        return $this->hydrateResult($this->esIndex->getType($this->getIndexType())->search(
            Elastica_Query::create($andContainer)->setLimit(100000)
        ));
    }

    public function getCategoryFacets(array $categories = array())
    {
        /**
         * Use elastic search's filter- or queryfacet here instead of this brutforce. ^^
         * Need to build one for elastica.
         */
        $query = Elastica_Query::create(new Elastica_Filter_Not(
            new Elastica_Filter_Term(array('attributes.marked_deleted' => TRUE))
        ));
        $facetname = 'categories-facet';
        $facet = new Elastica_Facet_Terms($facetname);
        $facet->setField('detailItem.category');
        $facet->setSize(2000);
        $query->addFacet($facet);
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        $resultData = $esType->search($query);
        $allFacets = $resultData->getFacets();

        $facetData = $allFacets[$facetname];
        $resultFacets = array();
        foreach ($facetData['terms'] as $facetResult)
        {
            if (in_array($facetResult['term'], $categories))
            {
                $resultFacets[] = $facetResult;
            }
        }
        return FinderResult::fromArray(array(
            'items' => $resultFacets,
            'totalCount' => count($resultFacets)
        ));
    }

    public function findAllImportIdentifiers(IDataSource $dataSource)
    {
        $query = Elastica_Query::create(NULL);
        $query->setFields(array('attributes.import_ids'));
        $sourceFilter = new Elastica_Filter_Prefix('attributes.import_ids', $dataSource->getName().':');
        $deletedFilter = new Elastica_Filter_Not(
            new Elastica_Filter_Term(array('attributes.marked_deleted' => TRUE))
        );
        $filter = new Elastica_Filter_And();
        $query->setFilter(
            $filter->addFilter($sourceFilter)->addFilter($deletedFilter)
        );
        $esType = $this->esIndex->getType($this->getIndexType());
        $resultData = $esType->search($query->setLimit(30000));
        
        $importIds = array();
        foreach ($resultData->getResults() as $hit)
        {
            $fields = $hit->getFields();
            if (is_array($fields['attributes.import_ids']))
            {
                foreach ($fields['attributes.import_ids'] as $importId)
                {
                    if (0 === strpos($importId, $dataSource->getName()))
                    {
                        $importIds[] = $importId;
                    }
                }   
            }
            elseif (0 === strpos($fields['attributes.import_ids'], $dataSource->getName()))
            {
                $importIds[] = $fields['attributes.import_ids'];
            }
        }
        return $importIds;
    }

    public function getByCategoryIds(array $categoryIds)
    {
        $categoriesEqual = new Elastica_Filter_Terms();
        $categoriesEqual->setTerms('detailItem.category', $categoryIds);

        $notDeleted = new Elastica_Filter_Not(
            new Elastica_Filter_Term(
                array('attributes.marked_deleted' => TRUE)
            )
        );

        $andContainer = new Elastica_Filter_And();
        $andContainer->addFilter($categoriesEqual);
        $andContainer->addFilter($notDeleted);

        $query = Elastica_Query::create($andContainer);
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        return $this->hydrateResult(
            $esType->search($query->setLimit(100000))
        );
    }

    public function getByLastImportIds(array $importIds)
    {
        $importIdFilter = new Elastica_Filter_Terms();
        $importIdFilter->setTerms('attributes.import_ids', $importIds);

        $notDeleted = new Elastica_Filter_Not(
            new Elastica_Filter_Term(
                array('attributes.marked_deleted' => TRUE)
            )
        );

        $andContainer = new Elastica_Filter_And();
        $andContainer->addFilter($importIdFilter);
        $andContainer->addFilter($notDeleted);

        $query = Elastica_Query::create($andContainer);
        $esType = $this->esIndex->getType(
            $this->getIndexType()
        );
        return $this->hydrateResult(
            $esType->search($query->setLimit(100000))
        );
    }

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
