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
        );

        return $this->hydrateResult($this->esIndex->getType($this->getIndexType())->search(
            Elastica_Query::create($andContainer)->setLimit(100000)
        ));
    }

    public function findPotentialDuplicates(ShofiWorkflowItem $shofiItem)
    {
        $name = $shofiItem->getCoreItem()->getName();
        $location = $shofiItem->getCoreItem()->getLocation();
        $lonLat = $location->getCoordinates();
        $geoField = 'coreItem.location.coordinates';

        $filter = NULL;
        $nameFilter = NULL;
        if (! empty($name))
        {
            $nameFilter = new Elastica_Filter_Term(array('coreItem.name.raw' => $name));
            $filter = $nameFilter;
        }
        
        $geoFilter = NULL;
        if (isset($lonLat['lon']) && isset($lonLat['lat']))
        {
            $geoFilter = new Elastica_Filter_GeoDistance($geoField, $lonLat['lon'], $lonLat['lat'], '0.05km');
            $filter = ! $nameFilter ? $geoFilter : $filter;
        }

        if ($nameFilter && $geoFilter)
        {
            $filter = new Elastica_Filter_Or();
            $filter->addFilter($nameFilter)->addFilter($geoFilter);
        }

        $result = NULL;
        if ($filter)
        {
            $duplicatesFilter = new Elastica_Filter_And();
            $duplicatesFilter->addFilter(new Elastica_Filter_Not(
                new Elastica_Filter_Ids($this->getIndexType(), array($shofiItem->getIdentifier()))
            ));
            $duplicatesFilter->addFilter($filter);

            $result = $this->hydrateResult(
                $this->esIndex->getType($this->getIndexType())
                     ->search(Elastica_Query::create($duplicatesFilter)->setLimit(1000))
            );    
        }
        else
        {
            $result = FinderResult::fromArray(array());
        }
        
        return $result;
    }

    public function getCategoryFacets(array $categories = array())
    {
        /**
         * Use elastic search's filter- or queryfacet here instead of this brutforce. ^^
         * Need to build one for elastica.
         */
        $query = Elastica_Query::create(NULL);
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

    public function findItemByImportIdentifier($importIdentifier)
    {
        $listState = ListState::fromArray(array(
            'offset' => 0,
            'limit' => 1, 
            'sortDirection' => 'asc',
            'sortField' => 'name',
            'filter' => array('attributes.import_ids' => array($importIdentifier))
        ));
        $result = $this->find($listState);
        $resultItems = $result->getItems();
        if (1 < $result->getTotalCount())
        {
            // @todo The same import-identifier more than once. This shouldn't happen. How to handle?
        }

        return (0 < $result->getTotalCount()) ? $resultItems[0] : NULL;
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

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
