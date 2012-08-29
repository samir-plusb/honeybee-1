<?php

/**
 * The TipEventsXmlDataSourceConfig class is a concrete implementation of the WorkflowItemDataImportConfig base class.
 * It provides basic configuration for EventsDataImports.
 *
 * @version         $Id: TipEventsXmlDataSourceConfig.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsXmlDataSourceConfig extends DataSourceConfig
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
     * Holds the name of our the people_catalog setting,
     * that is used feed the PersonFileProvider.
     */
    const CFG_PEOPLE_CATALOG = 'people_catalog';

    /**
     * Holds the name of our the locations_catalog setting,
     * that is used feed the LocationIdProvider.
     */
    const CFG_LOCATIONS_CATALOG = 'locations_catalog';

    /**
     * Holds the name of our the schema setting,
     * that is used to validate our tip xml files.
     */
    const CFG_SCHEMA = 'schema';

    /**
     * Holds the name of our the articles_catalog setting,
     * that is used feed the ArticleFileProvider.
     */
    const CFG_ARTICLES_CATALOG = 'articles_catalog';

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
                self::CFG_PEOPLE_CATALOG,
                self::CFG_ARTICLES_CATALOG,
                self::CFG_LOCATIONS_CATALOG,
                self::CFG_SRC_DIRECTORY
            )
        );
    }

    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
}
