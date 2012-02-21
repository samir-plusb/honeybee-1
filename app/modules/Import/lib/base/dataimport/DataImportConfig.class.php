<?php

/**
 * The DataImportConfig class is an abstract implementation of the SimpleConfig base class.
 * It serves as the base for all IDataImport related config implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage      Base
 */
abstract class DataImportConfig extends SimpleConfig
{
    // ---------------------------------- <IImportConfig IMPL> -----------------------------------

    /**
     * Return an array with setting names, that we consider required.
     *
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array();
    }

    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>