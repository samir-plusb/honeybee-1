<?php

/**
 * The ImportFactory class is a concrete implementation of the IImportFactory interface.
 * It provides factory methods for creating IDataImport and IDataSource instances
 * based on a given DataImport- and DataSourceConfig objects that hold the details.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Factory
 */
class ImportFactory implements IImportFactory
{
    const CONFIG_CLASS_SUFFIX = 'Config';

    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our config object.
     *
     * @var         IImportFactoryConfig
     */
    protected $factoryConfig;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRCUTOR> ------------------------------------------

    /**
     * Creates a new ImportFactory instance.
     *
     * @param       IImportFactoryConfig $factoryConfig
     *
     * @throws      ImportFactoryException If an invalid configuration is given.
     */
    public function __construct(IImportFactoryConfig $factoryConfig)
    {
        $this->factoryConfig = $factoryConfig;
    }

    public static function create()
    {
        return new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );
    }

    // ---------------------------------- </CONSTRCUTOR> -----------------------------------------

    /**
     * Fetch the factory information for the given import $name
     * from our config and build it.
     *
     * @param       string $name
     * @param       array $parameters
     *
     * @return      IDataImport
     *
     * @throws      ImportFactoryException
     */
    public function createDataImport($name, array $parameters = array())
    {
        $importConfig = $this->factoryConfig->getDataImportConfig($name);

        $settings = isset($importConfig[DataImportsFactoryConfig::CFG_SETTINGS])
            ? $importConfig[DataImportsFactoryConfig::CFG_SETTINGS]
            : array();

        $importSettings = array_merge($settings, $parameters);
        $importClass = $importConfig[DataImportsFactoryConfig::CFG_CLASS];

        if (!class_exists($importClass))
        {
            throw new ImportFactoryException(
                "Unable to find provided import class: " . $importClass
            );
        }

        // This is a simple convention that prevents cross package dependencies
        // concerning the usage of config objects.
        // We always want an ImperiaDataImport to use a ImperiaDataImportConfig and
        // not a config object from a base or other domain package.
        // So it is ok to enforce the creation of concrete config objects when for concrete
        // concrete instances of IDataImport or IDataSource.
        $configClass = $importClass . self::CONFIG_CLASS_SUFFIX;

        if (!class_exists($configClass))
        {
            throw new ImportFactoryException(
                "Unable to find corresponding config class for import class: " . $importClass .
                ". Please make sure that you have create a " . $configClass . " implementation along with your " .
                $importClass
            );
        }

        return new $importClass(
            new $configClass($importSettings)
        );
    }

    /**
     * Fetch the factory information for the given datasource $name
     * from our config and build it.
     *
     * @param       string $name
     * @param       array $parameters
     *
     * @return      IDataSource
     *
     * @throws      ImportFactoryException
     */
    public function createDataSource($name, array $parameters = array())
    {
        $dataSourceConfig = $this->factoryConfig->getDataSourceConfig($name);
        $recordType = $dataSourceConfig[DataSourcesFactoryConfig::CFG_RECORD_TYPE];

        if (!class_exists($recordType))
        {
            throw new ImportFactoryException(
                "Unable to find provided datasource record implementor: " . $recordType
            );
        }

        $settings = isset($dataSourceConfig[DataSourcesFactoryConfig::CFG_SETTINGS])
            ? $dataSourceConfig[DataSourcesFactoryConfig::CFG_SETTINGS]
            : array();

        $dataSourceSettings = array_merge(
            $settings,
            $parameters,
            array(
                DataSourceConfig::CFG_RECORD_TYPE => $recordType
            )
        );

        $dataSourceClass = $dataSourceConfig[DataSourcesFactoryConfig::CFG_CLASS];

        if (!class_exists($dataSourceClass))
        {
            throw new ImportFactoryException(
                "Unable to find provided data source class: " . $dataSourceClass
            );
        }

        $configClass = $dataSourceClass . self::CONFIG_CLASS_SUFFIX;

        if (!class_exists($configClass))
        {
            throw new ImportFactoryException(
                "Unable to find corresponding config class for datasource class: " . $dataSourceClass .
                ". Please make sure that you have create a " . $configClass . " implementation along with your" .
                $dataSourceClass
            );
        }

        return new $dataSourceClass(
            new $configClass($dataSourceSettings),
            $name,
            $dataSourceConfig['description']
        );
    }

    /**
     * Return an array of datasources that are configured for the given import.
     * If $onlyThese is provided, only datasources that are also in the passed array are returned.
     *
     * @param       string $name
     * @param       array $onlyThese
     *
     * @return      array
     */
    public function createDataSourcesForImport($name, array $onlyThese = array())
    {
        $importConfig = $this->factoryConfig->getDataImportConfig($name);
        $dataSources = array();

        if (isset($importConfig[DataImportsFactoryConfig::CFG_DATASOURCES]))
        {
            $dataSourceNames = $importConfig[DataImportsFactoryConfig::CFG_DATASOURCES];

            if (is_array($dataSourceNames))
            {
                foreach ($dataSourceNames as $dataSourceName)
                {
                    if (empty($onlyThese) || in_array($dataSourceName, $onlyThese))
                    {
                        $dataSources[] = $this->createDataSource($dataSourceName);
                    }
                }
            }
            elseif (is_string($dataSourceNames))
            {
                $dataSources[] = $this->createDataSource($dataSourceNames);
            }
        }

        return $dataSources;
    }
}

?>