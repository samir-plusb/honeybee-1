<?php

/**
 * The ImperiaDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the ImperiaDataSource class.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Import/Imperia
 */
class ImperiaDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Name of our doc_ids config parameter, that can be optionally be provided
     * and is used during testing in situations when we don't the data source to trigger requests.
     */
    const CFG_DOCIDS = 'doc_ids';

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
                self::CFG_ACCOUNT_PASS
            )
        );
    }

    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>