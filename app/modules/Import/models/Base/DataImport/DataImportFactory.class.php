<?php

class DataImportFactory implements IDataImportFactory
{
    protected $factoryConfig;

    public function __construct(DataImportFactoryConfig $factoryConfig)
    {
        $this->factoryConfig = $factoryConfig;
    }

    public function createDataImport($configClass, array $parameters = array())
    {
        $importSettings = array_merge(
            $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_SETTINGS)
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
