<?php

/**
 * The AssetModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Setup
 */
class AssetDatabaseSetup extends BaseCouchDatabaseSetup
{
    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->setDatabase(
            AgaviContext::getInstance()->getDatabaseConnection('Assets.Write')
        );
    }

    /**
     * get the source directory for map and reduce javascript files
     */
    public function getSourceDirectory()
    {
        return __DIR__.'/views';
    }

    public function setup($tearDownFirst = FALSE)
    {
        parent::setup($tearDownFirst);

        error_log("SETTING UP THE ASSET DATABASE(teardown: $tearDownFirst)");

        $documentStore = new CouchDocumentStore($this->getDatabase());
        $documentStore->save(
            IdSequenceId::fromArray(array(
                'identifier' => AssetIdSequence::COUCHDB_DOCID,
                'currentId' => 0
            )
        ));
    }
}

?>