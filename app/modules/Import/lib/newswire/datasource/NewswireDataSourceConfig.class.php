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
     * Holds the name of our 'path' setting that exposes the filesystem
     * path, where we will look for the files to import.
     */
    const CFG_DIRECTORY_PATH = 'path';

    /**
     * Holds the name of our 'regexp' setting that exposes the pattern
     * that we use to filter files on the fs-path which we are traversing.
     */
    const CFG_REGEXP = 'regexp';

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
                self::CFG_REGEXP,
                self::CFG_DIRECTORY_PATH,
                self::CFG_TIMESTAMP_FILE
            )
        );
    }

    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>