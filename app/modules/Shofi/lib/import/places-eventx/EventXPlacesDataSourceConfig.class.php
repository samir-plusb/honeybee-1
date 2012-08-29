<?php

/**
 * The EventXPlacesDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the HotelDataSource class.
 *
 * @version         $Id: EventXPlacesDataSourceConfig.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/EventX
 */
class EventXPlacesDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our the files setting,
     * that is used to configure our source directory.
     */
    const CFG_SRC_DIRECTORY = 'source_directory';


    /**
     * Holds the name of our the files setting,
     * that is used to configure our source file.
     */
    const CFG_FILES = 'files';

    /**
     * Holds the name of our the schema setting,
     * that is used to validate our tip xml files.
     */
    const CFG_SCHEMA = 'schema';

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
                self::CFG_FILES,
                self::CFG_SCHEMA,
                self::CFG_SRC_DIRECTORY
            )
        );
    }

    // ---------------------------------- </DataSourceConfig OVERRIDES> --------------------------
}

?>
