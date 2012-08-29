<?php

/**
 * The WkgDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the WkgDataSource class.
 *
 * @version         $Id: TheaterDataSourceConfig.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Tip
 */
class TipRestaurantDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Setting that holds the directory where to look for wkg data (xml files).
     */
    const CFG_FILE_PATH = 'file_path';

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
                self::CFG_FILE_PATH
            )
        );
    }

    // ---------------------------------- </IConfig IMPL> ----------------------------------
}

?>
