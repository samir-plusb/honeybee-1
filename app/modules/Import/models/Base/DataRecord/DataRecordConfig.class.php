<?php

/**
 * The DataRecordConfig class is a concrete implementation of the SimpleConfig base class.
 * It serves as the base for all IDataRecord related config implementations.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class DataRecordConfig extends SimpleConfig
{
    const CFG_ORIGIN = 'origin';
    
    const CFG_SOURCE = 'source';
    
    // ---------------------------------- <IImportConfig IMPL> -----------------------------------
    
    /**
     * Return an array with setting names, that we consider required.
     * 
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array(
            self::CFG_ORIGIN,
            self::CFG_SOURCE
        );
    }
    
    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>