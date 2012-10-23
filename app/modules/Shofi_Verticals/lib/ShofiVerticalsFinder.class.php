<?php

/**
 * The ShofiVerticalsFinder is responseable for finding shofi-verticals and provides several methods to do so.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 */
class ShofiVerticalsFinder extends BaseFinder
{
    const INDEX_TYPE = 'shofi-vertical';

    public static function create(IListConfig $listConfig)
    {
        return new self(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                self::getElasticSearchDatabaseName()
            )->getResource(),
            $listConfig,
            ShofiVerticalsWorkflowService::getInstance()
        );
    }

    public static function getElasticSearchDatabaseName()
    {
        $connections = AgaviConfig::get('shofi_verticals.connections');
        return $connections['elasticsearch'];
    }

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
