<?php

/**
 * The MoviesDatabaseSetup is responseable for setting up our module for usage.
 *
 * @version         $Id: MoviesDatabaseSetup.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink
 * @package         Movies
 * @subpackage      Setup
 */
class MoviesDatabaseSetup extends BaseCouchDatabaseSetup
{
    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->setDatabase(
            AgaviContext::getInstance()->getDatabaseConnection(
                MoviesWorkflowSupervisor::getCouchDbDatabasename()
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