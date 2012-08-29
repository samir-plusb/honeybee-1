<?php

/**
 * The GdocHotelDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the GdocHotelDataSource class.
 *
 * @version         $Id: GdocHotelDataSourceConfig.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel-Gdoc
 */
class GdocHotelDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Setting that holds the document id to pull the gdoc spreadsheet from.
     */
    const CFG_DOC_ID = 'doc_id';

    /**
     * Setting that holds the email used to auth against the google api.
     */
    const CFG_EMAIL = 'email';

    /**
     * Setting that holds the password used to auth against the google api.
     */
    const CFG_PASSWORD = 'password';

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
                self::CFG_DOC_ID,
                self::CFG_EMAIL,
                self::CFG_PASSWORD
            )
        );
    }

    // ---------------------------------- </IConfig IMPL> ----------------------------------
}

?>
