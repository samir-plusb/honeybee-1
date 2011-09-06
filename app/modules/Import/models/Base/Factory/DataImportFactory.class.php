<?php

class DataImportFactory implements IDataImportFactory
{
    protected $factoryConfig;

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