<?php

class DataImportFactory
{
    protected $factoryConfig;

    public function __construct(DataImportFactoryConfig $factoryConfig)
    {
        $this->factoryConfig = $factoryConfig;
    }

    public function createDataImport($configClass)
    {
        $importSettings = array_merge(
            $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_SETTINGS)
        );

        $importConfig = new $configClass($importSettings);
        $importClass = $config->getSetting(DataImportFactoryConfig::CFG_CLASS);

        return new $importClass($importConfig);
    }

    public function createDataSource($configClass)
    {
        $dataSourceSettings = $this->factoryConfig->getSetting(DataImportFactoryConfig::CFG_DATASRC);

        $dataSourceSettings = array_merge(
            $dataSourceSettings[ImperiaDataSourceConfig::CFG_SETTINGS],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE     => $dataSourceSettings['record'],
                ImperiaDataSourceConfig::CFG_DOCUMENT_IDS    => self::$docIds
            )
        );

        $dataSrcConfig = new $configClass($dataSourceSettings);
        $dataSourceClass = $dataSourceSettings[DataImportFactoryConfig::CFG_CLASS];

        return new $dataSourceClass($dataSrcConfig);
    }
}

?>
