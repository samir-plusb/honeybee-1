<?php

/**
 * The ImportBaseDataSource class is a concrete implementation of the IDataImportFactory interface.
 * It provides factory methods for creating IDataImport and IDataSource instances
 * based on a given DataImportFactoryConfig.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class DataImportFactory implements IDataImportFactory
{
    const CONFIG_CLASS_SUFFIX = 'Config';
    
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
     * @param       array $parameters
     * 
     * @return      IDataImport
     */
    public function createDataImport(array $parameters = array())
    {
        $importSettings = array_merge(
            $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_SETTINGS),
            $parameters
        );

        $importClass = $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_CLASS);
        
        if (!class_exists($importClass))
        {
            throw new DataImportFactoryException(
                "Unable to find provided import class: " . $importClass
            );
        }
        
        // This is a simple convention that prevents cross package dependencies 
        // concerning the usage of config objects.
        // We always want an ImperiaDataImport to use a ImperiaDataImportConfig and 
        // not a config object from a base or other domain level package.
        // So it is ok to enforce the creation of concrete config objects when implementing
        // concrete instances of other packages such as DataImport or DataSource.
        $configClass = $importClass . self::CONFIG_CLASS_SUFFIX;
        
        if (!class_exists($configClass))
        {
            throw new DataImportFactoryException(
                "Unable to find corresponding config class for import class: " . $importClass . 
                ". Please make sure that you have create a " . $configClass . " implementation along with your" . 
                $importClass
            );
        }
        
        $importConfig = new $configClass($importSettings);

        return new $importClass($importConfig);
    }
    
    /**
     * Create a new concrete IDataSource instance based on our config
     * and optionally provided parameters.
     * 
     * @param       array $parameters
     * 
     * @return      IDataSource
     */
    public function createDataSource(array $parameters = array())
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
        
        if (!class_exists($dataSourceClass))
        {
            throw new DataImportFactoryException(
                "Unable to find provided data source class: " . $dataSourceClass
            );
        }
        
        $configClass = $dataSourceClass . self::CONFIG_CLASS_SUFFIX;
        
        if (!class_exists($configClass))
        {
            throw new DataImportFactoryException(
                "Unable to find corresponding config class for datasource class: " . $dataSourceClass . 
                ". Please make sure that you have create a " . $configClass . " implementation along with your" . 
                $dataSourceClass
            );
        }
        
        $dataSourceConfig = new $configClass($dataSourceSettings);

        return new $dataSourceClass($dataSourceConfig);
    }
}

?>