<?php

/**
 * The CouchDbDataImportConfig class is a concrete implementation of the DataImportConfig base class.
 * It provides basic configuration for CouchDbDataImports.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataImport
 * 
 * @see             CouchDbDataImport
 */
class CouchDbDataImportConfig extends DataImportConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * @const       PARAM_BUFFER_SIZE Name of the config parameter that holds our buffer size.
     */
    const PARAM_BUFFER_SIZE = 50;
    
    /**
     * @const       CFG_COUCHDB_HOST Name of the config setting that holds our couchdb host.
     */
    const CFG_COUCHDB_HOST = 'couchdb_host';
    
    /**
     * @const       CFG_COUCHDB_PORT Name of the config setting that holds our couchdb port.
     */
    const CFG_COUCHDB_PORT = 'couchdb_port';
    
    /**
     * @const       CFG_COUCHDB_DATABASE Name of the config setting that holds our couchdb database name.
     */
    const CFG_COUCHDB_DATABASE = 'couchdb_database';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <DataImportConfig OVERRIDES> ---------------------------
    
    /**
     * Return an array of settings names,
     * that must be provided by our config srource.
     * 
     * @return      array
     * 
     * @see         DataImportConfig::getRequiredSettings()
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_COUCHDB_HOST,
                self::CFG_COUCHDB_PORT,
                self::CFG_COUCHDB_DATABASE
            )
        );
    }
    
    // ---------------------------------- </DataImportConfig OVERRIDES> --------------------------
}

?>