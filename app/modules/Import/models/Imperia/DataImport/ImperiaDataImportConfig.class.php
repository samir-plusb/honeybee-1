<?php

class ImperiaDataImportConfig extends DataImportConfig
{
    const CFG_COUCHDB_HOST = 'couchdb_host';
    
    const CFG_COUCHDB_PORT = 'couchdb_port';
    
    const CFG_COUCHDB_DATABASE = 'couchdb_database';
    
    public function getRequiredSettings()
    {
        return array(
            self::CFG_COUCHDB_HOST,
            self::CFG_COUCHDB_PORT,
            self::CFG_COUCHDB_DATABASE
        );
    }
}

?>
