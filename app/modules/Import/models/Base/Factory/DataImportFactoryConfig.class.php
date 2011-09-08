<?php

/**
 * The DataImportFactoryConfig class is an abstract implementation of the XmlFileBasedConfig base class.
 * It serves as the base for all IDataImportFactory related config implementations.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataSource
 */
class DataImportFactoryConfig extends XmlFileBasedConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Name of the 'class' setting, which defines the IDataImport implementation to use,
     * for serving createDataImport requests.
     * 
     * @const       CFG_CLASS
     */
    const CFG_CLASS = 'class';
    
    /**
     * Name of the 'class' setting, which defines the IDataImport implementation to use,
     * for serving createDataImport requests.
     * 
     * @const       CFG_NAME
     */
    const CFG_NAME = 'name';

    /**
     * Name of the 'class' setting, which defines the IDataImport implementation to use,
     * for serving createDataImport requests.
     * 
     * @const       CFG_DESCRIPTION
     */
    const CFG_DESCRIPTION = 'description';

    /**
     * Name of the 'settings' setting, which defines the settings to use to initialize
     * the IDataImport's config object.
     * 
     * @const       CFG_SETTINGS
     */
    const CFG_SETTINGS = 'settings';

    /**
     * Name of the 'datasource' setting, which holds the definition of the datasource 
     * to use for the import that we reflect.
     * 
     * @const       CFG_DATASRC
     */
    const CFG_DATASRC = 'datasource';
    
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    
    // ---------------------------------- <IImportConfig IMPL> -----------------------------------
    
    /**
     * Return an array with setting names, that we consider required.
     * 
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array(
            self::CFG_CLASS,
            self::CFG_NAME,
            self::CFG_DESCRIPTION,
            self::CFG_SETTINGS,
            self::CFG_DATASRC
        );
    }
    
    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>