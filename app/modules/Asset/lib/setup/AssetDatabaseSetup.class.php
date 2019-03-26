<?php

use Honeybee\Agavi\Database\CouchDb\DatabaseSetup;

/**
 * The AssetModuleSetup is responseable for setting up our module for usage.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Setup
 */
class AssetDatabaseSetup extends DatabaseSetup
{
    public function execute(AgaviDatabase $database, $tearDownFirst = FALSE)
    {
        parent::execute($database, $tearDownFirst);

        error_log("SETTING UP THE ASSET DATABASE(teardown: $tearDownFirst)");

        $documentStore = new CouchDocumentStore($database->getConnection());
        $documentStore->save(
            IdSequenceId::fromArray(array(
                'identifier' => AssetIdSequence::COUCHDB_DOCID,
                'currentId' => 0
            )
        ));
    }
}