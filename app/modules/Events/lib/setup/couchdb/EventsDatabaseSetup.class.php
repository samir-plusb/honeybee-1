<?php

/**
 * The EventsDatabaseSetup is responseable for setting up our module for usage.
 *
 * @version         $Id: EventsDatabaseSetup.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink
 * @package         Events
 * @subpackage      Setup
 */
class EventsDatabaseSetup extends BaseCouchDatabaseSetup
{
    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->setDatabase(
            AgaviContext::getInstance()->getDatabaseConnection(
                EventsWorkflowSupervisor::getCouchDbDatabasename()
            )
        );
    }

    /**
     * get the source directory for map and reduce javascript files
     */
    public function getSourceDirectory()
    {
        return __DIR__.'/views';
    }
}

?>