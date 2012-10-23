<?php

/**
 * The MoviesFinder is responseable for finding movies and provides several methods to do so.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 */
class MoviesFinder extends BaseFinder
{
    const INDEX_TYPE = 'movie';

    public static function create(IListConfig $listConfig)
    {
        return new self(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                self::getElasticSearchDatabaseName()
            )->getResource(),
            $listConfig,
            MoviesWorkflowService::getInstance()
        );
    }

    public static function getElasticSearchDatabaseName()
    {
        $connections = AgaviConfig::get('movies.connections');
        return $connections['elasticsearch'];
    }

    public function findRelatedTheaters(MoviesWorkflowItem $movieItem)
    {
        $theaterImportIds = array();
        foreach ($movieItem->getMasterRecord()->getScreenings() as $screening)
        {
            if (! in_array($screening['theaterId'], $theaterImportIds))
            {
                $theaterImportIds[] = 'theaters-telavision:' . $screening['theaterId'];
            }
        }
        if (empty($theaterImportIds))
        {
            return array();
        }
        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));

        $theatersResult = $finder->getByLastImportIds($theaterImportIds);
        return $theatersResult->getItems();
    }

    public function findRelatedMovies(ShofiWorkflowItem $theaterItem)
    {
        foreach ($theaterItem->getAttribute('import_ids', array()) as $importId)
        {
            $parts = explode(':', $importId);
            if (2 == count($parts) && 'theaters-telavision' === $parts[0])
            {
                $importId = $parts[1];
            }
        }
        if (! $importId)
        { // shouldn't happen
            error_log("Uncountered a referenced theater without import origin data.");
            return array();
        }

        $result = $this->find(ListState::fromArray(array(
            'offset' => 0,
            'limit' => 500,
            'filter' => array('masterRecord.screenings.theaterId' => $importId)
        )));

        return $result->getItems();
    }

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
