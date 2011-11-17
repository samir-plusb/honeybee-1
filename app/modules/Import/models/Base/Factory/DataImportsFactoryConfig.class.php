<?php

/**
 * The DataImportsFactoryConfig class is a concrete implementation of the DataImportsFactoryConfig base class.
 * It holds the factory-config details for any available IDataImport.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class DataImportsFactoryConfig extends XmlFileBasedConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Name of the 'dataimports' node, which defines our available dataimports.
     */
    const CFG_DATAIMPORTS = 'dataimports';
    
    /**
     * Holds the name of our the dataimport specific 'settings' setting.
     */
    const CFG_SETTINGS = 'settings';
    
    /**
     * Holds the name of our the datasourc specific 'description' setting.
     */
    const CFG_DESCRIPTION = 'description';
    
    /**
     * Holds the name of our the dataimport specific 'class' setting.
     */
    const CFG_CLASS = 'class';
    
    /**
     * Holds the name of our the dataimport specific 'datasources' setting.
     */
    const CFG_DATASOURCES = 'datasources';
    
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
            self::CFG_DATAIMPORTS
        );
    }
    
    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>