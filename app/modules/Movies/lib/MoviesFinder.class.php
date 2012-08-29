<?php

/**
 * The MoviesFinder is responseable for finding movies and provides several methods to do so.
 *
 * @version         $Id: MoviesFinder.class.php 1086 2012-04-18 21:29:31Z tschmitt $
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
        $theaterIds = array();
        foreach ($movieItem->getMasterRecord()->getScreenings() as $screening)
        {
            if (! in_array($screening['theaterId'], $theaterIds))
            {
                $theaterIds[] = $screening['theaterId'];
            }
        }
        if (empty($theaterIds))
        {
            return array();
        }

        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));

        $theatersResult = $finder->findByIds($theaterIds);
        return $theatersResult->getItems();
    }

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
