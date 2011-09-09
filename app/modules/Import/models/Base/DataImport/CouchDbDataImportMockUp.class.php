<?php

/**
 * The CouchDbDataImportMockUp class extends ImperiaDataImport for testing purposes
 * and extends it's import behaviour to writing the processed data to a file for test assertion.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class CouchDbDataImportMockUp extends CouchDbDataImport
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of the config parameter that specifies the outputfile to write to.
     */
    const SETTING_OUTPUT_FILE = 'output_file';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <CouchDbDataImport OVERRIDES> --------------------------
    
    /**
     * Setup things for logging our import data to a file for test assertion.
     * 
     * @param       IDataSource $dataSource 
     * 
     * @see         CouchDbDataImport::init()
     */
    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);
        
        $importTargetFile = $this->config->getSetting(self::SETTING_OUTPUT_FILE);
        
        file_put_contents($importTargetFile, '');
    }
    
    /**
     * After calling our parent log the import data to a file for later assertion.
     * 
     * @param       array $data 
     */
    protected function importData(array $data)
    {
        parent::importData($data);
        
        $importTargetFile = $this->config->getSetting(self::SETTING_OUTPUT_FILE);

        if (empty($importTargetFile) || !is_string($importTargetFile))
        {
            throw new DataImportException(
                "Missing or invalid output_file setting encountered for mockimport class. Path: " . $importTargetFile
            );
        }

        file_put_contents($importTargetFile, var_export($data, TRUE), FILE_APPEND);
    }
    
    // ---------------------------------- </CouchDbDataImport OVERRIDES> -------------------------
}

?>