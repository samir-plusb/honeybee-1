<?php

/**
 * IImportFactory implementations are responseable for creating the
 * IDataImport and IDataSource instances that are reflected by a given ImportFactoryConfig.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
interface IImportFactory
{
    /**
     * Create a new concrete IDataImport instance by $name.
     * Optionally passed parameters will be merged into the config object together
     * with any exsisting settings and override them on conflict.
     * 
     * @param       string $name
     * @param       array $parameters
     * 
     * @return      IDataImport
     */
    public function createDataImport($name, array $parameters = array());

    /**
     * Create a new concrete IDataSource by $name.
     * Optionally passed parameters will be merged into the config object together
     * with any exsisting settings and override them on conflict.
     * 
     * @param       string $name
     * @param       array $parameters
     * 
     * @return      IDataSource
     */
    public function createDataSource($name, array $parameters = array());
}

?>