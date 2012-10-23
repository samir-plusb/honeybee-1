<?php

/**
 * The EventsFinder is responseable for finding Events and provides several methods to do so.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Events
 */
class EventsFinder extends BaseFinder
{
    const INDEX_TYPE = 'event';

    public static function create(IListConfig $listConfig)
    {
        return new self(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                self::getElasticSearchDatabaseName()
            )->getResource(),
            $listConfig,
            EventsWorkflowService::getInstance()
        );
    }

    public static function getElasticSearchDatabaseName()
    {
        $connections = AgaviConfig::get('events.connections');
        return $connections['elasticsearch'];
    }

    public function findRelatedLocations(EventsWorkflowItem $eventItem)
    {
        $locationIds = array();
        foreach ($eventItem->getMasterRecord()->getEventSchedule()->getLocations() as $eventLocation)
        {
            $locationId = $eventLocation->getLocationId();
            if (! empty($locationId))
            {
                $locationIds[] = $locationId;
            }
        }
        if (empty($locationIds))
        {
            return array();
        }

        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $result = $finder->findByIds($locationIds);

        return $result->getItems();
    }

    protected function getIndexType()
    {
        return self::INDEX_TYPE;
    }
}

?>
