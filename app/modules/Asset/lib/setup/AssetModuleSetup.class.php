<?php

/**
 * The AssetModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Setup
 */
class AssetModuleSetup extends BaseCouchDatabaseSetup
{
    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->setDatabase(
            AgaviContext::getInstance()->getDatabaseConnection('CouchAssets')
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

        $idSequenceDoc = array(
            '_id' => AssetIdSequence::COUCHDB_DOCID,
            'curId' => 0
        );

        $this->getDatabase()->storeDoc(NULL, $idSequenceDoc);
    }
}

?>