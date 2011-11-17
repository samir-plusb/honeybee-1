<?php

/**
 * The DataSourceConfig class is an abstract implementation of the SimpleConfig base class.
 * It serves as the base for all IDataSource related config implementations.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class DataSourceConfig extends SimpleConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Setting that holds the IDataRecord implementation to use for creating new concrete records.
     */
    const CFG_RECORD_TYPE = 'record';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <IImportConfig IMPL> -----------------------------------
    
    /**
     * Return an array with setting names, that we consider required.
     * 
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array(
            self::CFG_RECORD_TYPE
        );
    }
    
    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>