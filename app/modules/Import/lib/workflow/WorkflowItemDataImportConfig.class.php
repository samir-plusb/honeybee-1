<?php

/**
 * The WorkflowItemDataImportConfig class is a concrete implementation of the DataImportConfig base class.
 * It provides basic configuration for WorkflowItemDataImports.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Workflow
 * 
 * @see             WorkflowItemDataImport
 */
class WorkflowItemDataImportConfig extends DataImportConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Name of the config setting that holds our couchdb database name.
     */
    const CFG_NOTIFY_SUPERVISOR = 'notify';
    
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
                self::CFG_NOTIFY_SUPERVISOR
            )
        );
    }
    
    // ---------------------------------- </DataImportConfig OVERRIDES> --------------------------
}

?>