<?php

/**
 * The ItemsModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Setup
 */
class ItemsModuleSetup extends BaseCouchDatabaseSetup
{
    /**
     * Holds the name of our couchdb database.
     */
    const COUCHDB_DATABASE = 'midas_import';


    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        /* @var AgaviDatabase */
        $database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase(self::COUCHDB_DATABASE);
        $this->setDatabase($database->getConnection());
    }

    /**
     * get the source directory for map and reduce javascript files
     *
     * @return string
     */
    public function getSourceDirectory()
    {
        return __DIR__.'/views';
    }
}

?>