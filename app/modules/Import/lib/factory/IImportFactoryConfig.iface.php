<?php

/**
 * IImportFactoryConfig implementations are responseable for providing factory configuration,
 * that can be used to build instances of IDataImports and IDataSources.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Factory
 */
interface IImportFactoryConfig
{
    /**
     * Return the factory config for a data-import instance by name.
     *
     * @return      array
     */
    public function getDataImportConfig($name);

    /**
     * Return the factory config for a data-source instance by name.
     *
     * @return      array
     */
    public function getDataSourceConfig($name);
}

?>