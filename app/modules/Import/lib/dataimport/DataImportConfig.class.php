<?php

/**
 * The DataImportConfig class is an abstract implementation of the SimpleConfig base class.
 * It serves as the base for all IDataImport related config implementations.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage DataImport
 */
abstract class DataImportConfig extends SimpleConfig
{
    // ---------------------------------- <IConfig IMPL> -----------------------------------

    /**
     * Return an array with setting names, that we consider required.
     *
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array();
    }

    // ---------------------------------- </IConfig IMPL> ----------------------------------
}

?>