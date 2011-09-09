<?php

/**
 * IDataImportFactory implementations are responseable for creating the
 * IDataImport and IDataSource instances that are reflected by a given DataImportFactoryConfig.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
interface IDataImportFactory
{
    /**
     * Create a new concrete IDataImport instance using the given config class
     * as the concrete config implementor to provide to the created IDataImport.
     * Optionally passed parameters will be merged into the config object together
     * with any exsisting settings and override them on conflict.
     * 
     * @param       string $configClass
     * @param       array $parameters
     * 
     * @return      IDataImport
     */
    public function createDataImport(array $parameters = array());

    /**
     * Create a new concrete IDataSource instance using the given config class
     * as the concrete config implementor to provide to the created IDataSource.
     * Optionally passed parameters will be merged into the config object together
     * with any exsisting settings and override them on conflict.
     * 
     * @param       string $configClass
     * @param       array $parameters
     * 
     * @return      IDataSource
     */
    public function createDataSource(array $parameters = array());
}

?>