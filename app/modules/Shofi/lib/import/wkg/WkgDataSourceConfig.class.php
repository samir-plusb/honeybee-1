<?php

/**
 * The WkgDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the WkgDataSource class.
 *
 * @version         $Id: RssDataSourceConfig.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Wkg
 */
class WkgDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Setting that holds the directory where to look for wkg data (xml files).
     */
    const CFG_DIRECTORY = 'directory';

    /**
     * Setting that holds the pattern(regexp) used to find wkg files.
     */
    const CFG_FILE_PATTERN = 'file_pattern';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <IConfig IMPL> -----------------------------------

    /**
     * Return an array with setting names, that we consider required.
     *
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_DIRECTORY,
                self::CFG_FILE_PATTERN
            )
        );
    }

    // ---------------------------------- </IConfig IMPL> ----------------------------------
}

?>
