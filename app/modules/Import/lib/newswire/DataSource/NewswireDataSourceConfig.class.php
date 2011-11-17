<?php

/**
 * The NewswireDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the NewswireDataSource class.
 * 
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Newswire
 */
class NewswireDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our 'glob' setting that exposes the glob pattern 
     * thah we use to find the file on the fs which we want to iterate.
     */
    const CFG_GLOB = 'glob';
    
    /**
     * Holds the name of our 'timestamp_file' setting that exposes the filename
     * of the file wa use to memoize our imports.
     */
    const CFG_TIMESTAMP_FILE = 'timestamp_file';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
    
    /**
     * Return an array of settings names,
     * that must be provided by our config source.
     * 
     * @return      array
     * 
     * @see         DataSourceConfig::getRequiredSettings()
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_GLOB,
                self::CFG_TIMESTAMP_FILE
            )
        );
    }
    
    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>