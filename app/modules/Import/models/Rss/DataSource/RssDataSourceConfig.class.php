<?php

/**
 * The RssDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the RssDataSource class.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Rss
 */
class RssDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our 'rss_url' setting that exposes the url that we fetch our rss from.
     */
    const CFG_RSS_URL = 'rss_url';
    
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
                self::CFG_RSS_URL
            )
        );
    }
    
    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>