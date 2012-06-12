<?php

/**
 * The ShofiCategoriesFinder is responseable for finding shofi-categories and provides several methods to do so.
 *
 * @version         $Id: ShofiCategoriesFinder.class.php 1086 2012-04-18 21:29:31Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 */
class ShofiCategoriesFinder extends BaseFinder
{
    const INDEX_TYPE = 'shofi-category';

    public static function create(IListConfig $listConfig)
    {
        return new self(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                self::getElasticSearchDatabaseName()
            )->getResource(),
            $listConfig,
            ShofiCategoriesWorkflowService::getInstance()
        );
    }

    public static function getElasticSearchDatabaseName()
    {
        $connections = AgaviConfig::get('shofi_categories.connections');
        return $connections['elasticsearch'];
    }

    public function getCategoryFacetForPlacesByVertical($verticalIdentifier)
    {
        $facets = array();
        // find all categories for the given vertical id.
        $listState = ListState::fromArray(array(
            'limit' => 2000,
            'filter' => array('masterRecord.vertical.id' => $verticalIdentifier)
        ));
        $result = $this->find($listState);
        foreach($result->getItems() as $categoryItem)
        {
            $facets[$categoryItem->getIdentifier()] = array(
                'identifier' => $categoryItem->getIdentifier(),
                'ticket_id' => $categoryItem->getTicketId(),
                'name' => $categoryItem->getMasterRecord()->getName(),
                'count' => 0
            );
        }
        // do facetting based on selected found categories
        $placesFinder = ShofiFinder::create(listConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $result = $placesFinder->getCategoryFacets(array_keys($facets));
        foreach ($result->getItems() as $categoryFacet)
        {
            $catIdentifer = $categoryFacet['term'];
            $facets[$catIdentifer]['count'] = $categoryFacet['count'];
        }
        return $facets;
    }

    public function getCategoriesByNames(array $categoryNames)
    {
        $categoryNamesEqual = new Elastica_Filter_Terms();
        $categoryNamesEqual->setTerms('masterRecord.name.raw', $categoryNames);

        $notDeleted = new Elastica_Filter_Not(
            new Elastica_Filter_Term(
                array('attributes.marked_deleted' => TRUE)
            )
        );

        $andContainer = new Elastica_Filter_And();
        $andContainer->addFilter($categoryNamesEqual);
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
