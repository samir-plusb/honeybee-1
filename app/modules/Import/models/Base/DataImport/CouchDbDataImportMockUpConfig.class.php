<?php

/**
 * The CouchDbDataImportMockUpConfig class extends the CouchDbDataImportConfig for testing purposes.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 * 
 * @see             CouchDbDataImport
 */
class CouchDbDataImportMockUpConfig extends CouchDbDataImportConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of the config parameter that specifies the outputfile to write to.
     */
    const CFG_OUTPUT_FILE = 'output_file';
    
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
                self::CFG_OUTPUT_FILE
            )
        );
    }
    
    // ---------------------------------- </DataImportConfig OVERRIDES> --------------------------
}

?>