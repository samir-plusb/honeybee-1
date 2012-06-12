<?php

/**
 * The DataSourcesFactoryConfig class is a concrete implementation of the DataImportsFactoryConfig base class.
 * It holds the factory-config details for any available IDataSource.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Factory
 */
class DataSourcesFactoryConfig extends AgaviXmlConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Name of the 'datasources' node, which defines our available datasources.
     */
    const CFG_DATASOURCES = 'datasources';

    /**
     * Holds the name of our the datasource specific 'settings' setting.
     */
    const CFG_SETTINGS = 'settings';

    /**
     * Holds the name of our the datasourc specific 'description' setting.
     */
    const CFG_DESCRIPTION = 'description';

    /**
     * Holds the name of our the datasource specific 'class' setting.
     */
    const CFG_CLASS = 'class';

    /**
     * Holds the name of our the datasource specific 'recordType' setting.
     */
    const CFG_RECORD_TYPE = 'recordType';

    // ---------------------------------- <CONSTANTS> --------------------------------------------


    // ---------------------------------- <IImportConfig IMPL> -----------------------------------

    /**
     * Return an array with setting names, that we consider required.
     *
     * @return      array
     */
    public function getRequiredSettings()
    {
        return array(
            self::CFG_DATASOURCES
        );
    }

    // ---------------------------------- </IImportConfig IMPL> ----------------------------------
}

?>