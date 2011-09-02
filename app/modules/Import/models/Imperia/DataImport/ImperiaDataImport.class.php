<?php

class ImperiaDataImport extends BaseDataImport
{
    protected $couchClient;

    protected function importRecord(IDataRecord $record)
    {
        $record->toArray();
    }

    /**
     * Setup our couch client and preselect the configured database,
     * so we can start sending data right away.
     */
    protected function init()
    {
        $this->couchClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
    }

    /**
     * Build the uri to use in order to connect to couchdb.
     *
     * @return string
     */
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            $this->config->getSetting(ImperiaDataImportConfig::CFG_COUCHDB_HOST),
            $this->config->getSetting(ImperiaDataImportConfig::CFG_COUCHDB_PORT)
        );
    }
}

?>
