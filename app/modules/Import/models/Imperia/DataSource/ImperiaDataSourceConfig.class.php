<?php

/**
 * The ImperiaDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the ImperiaDataSource class.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
class ImperiaDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Name of our doc_ids config parameter, that can be optionally be provided
     * and is used during testing in situations when we don't the data source to trigger requests.
     */
    const PARAM_DOCIDS = 'doc_ids';
    
    /**
     * Holds the name of our 'url' setting that exposes our imperia base url.
     */
    const CFG_URL = 'url';
    
    /**
     * Holds the name of our 'account_user' setting that exposes imperia's username postfield.
     */
    const CFG_ACCOUNT_USER = 'account_user';
    
    /**
     * Holds the name of our 'account_user' setting that exposes imperia's password postfield.
     */
    const CFG_ACCOUNT_PASS = 'account_pass';
    
    /**
     * Holds the name of our 'account_user' setting that exposes the url to our document-id update service.
     */
    const CFG_DOC_IDLIST_URL = 'doc_idlist_url';
    
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
                self::CFG_URL,
                self::CFG_ACCOUNT_USER,
                self::CFG_ACCOUNT_PASS,
                self::CFG_DOC_IDLIST_URL
            )
        );
    }
    
    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>