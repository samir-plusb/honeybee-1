<?php

/**
 * The ImportBaseDataSource class is a concrete implementation of the IDataImportFactory interface.
 * It provides factory methods for creating IDataImport and IDataSource instances based on a given DataImportFactoryConfig.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataSource
 */
class DataImportFactory implements IDataImportFactory
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our config object.
     * 
     * @var         DataImportFactoryConfig 
     */
    protected $factoryConfig;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRCUTOR> ------------------------------------------
    
    /**
     * Creates a new DataImportFactory instance.
     * 
     * @param       DataImportFactoryConfig $factoryConfig 
     * 
     * @throws      DataImportFactoryException If an invalid configuration is given.
     */
    public function __construct($factoryConfig)
    {
        if ($factoryConfig instanceof DataImportFactoryConfig) 
        {
            $this->factoryConfig = $factoryConfig;
        }
        elseif (is_string($factoryConfig))
        {
            $this->factoryConfig = new DataImportFactoryConfig($factoryConfig);
        }
        else
        {
            throw new DataImportFactoryException("Invalid factory config given.");
        }
    }
    
    // ---------------------------------- </CONSTRCUTOR> -----------------------------------------
    
    /**
     * Create a new concrete IDataImport instance based on our config
     * and optionally provided parameters.
     * 
     * @param       string $configClass
     * @param       array $parameters
     * 
     * @return      IDataImport
     */
    public function createDataImport($configClass, array $parameters = array())
    {
        $importSettings = array_merge(
            $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_SETTINGS),
            $parameters
        );

        $importConfig = new $configClass($importSettings);
        $importClass = $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_CLASS);

        return new $importClass($importConfig);
    }
    
    /**
     * Create a new concrete IDataSource instance based on our config
     * and optionally provided parameters.
     * 
     * @param       string $configClass
     * @param       array $parameters
     * 
     * @return      IDataSource
     */
    public function createDataSource($configClass, array $parameters = array())
    {
        $rawSourceSettings = $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_DATASRC);

        $dataSourceSettings = array_merge(
            $rawSourceSettings[DataImportFactoryConfig::CFG_SETTINGS],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE     => $rawSourceSettings['record']
            ),
            $parameters
        );

        $dataSourceClass = $rawSourceSettings[DataImportFactoryConfig::CFG_CLASS];
        $dataSrcConfig = new $configClass($dataSourceSettings);

        return new $dataSourceClass($dataSrcConfig);
    }
}

?>