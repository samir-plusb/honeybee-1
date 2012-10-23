<?php

/**
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Movies
 */
class MoviesWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'MoviesWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new MoviesWorkflowService('movies');
        }
        return self::$instance;
    }

    public function findItemByImportIdentifier($importIdentifier)
    {
        $listConfig = ListConfig::fromArray(AgaviConfig::get('movies.list_config'));
        $moviesFinder = MoviesFinder::create($listConfig);
        
        return $moviesFinder->findItemByImportIdentifier($importIdentifier);
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}
