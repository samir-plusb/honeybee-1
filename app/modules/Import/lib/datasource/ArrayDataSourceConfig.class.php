<?php

/**
 * The ArrayDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the ArrayDataSource class.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataSource
 */
class ArrayDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our imap 'password' setting that exposes our imap password.
     */
    const CFG_DATA = 'data';

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
                self::CFG_DATA
            )
        );
    }

    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
}

?>