<?php

/**
 * The ImportFactoryConfig class is an abstract implementation of the XmlFileBasedConfig base class.
 * It serves as the base for all IImportFactory related config implementations.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class ImportFactoryConfig extends ImportBaseConfig implements IImportFactoryConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Name of the 'dataimport_config' setting that holds our DataImportsFactoryConfig.
     */
    const CFG_IMPORTS_CONFIG = 'dataimports';
    
    /**
     * Name of the 'datasources_config' setting that holds our DataSourcesFactoryConfig.
     */
    const CFG_SOURCES_CONFIG = 'datasources';
    
    /**
     * Holds the name of the file that contains our dataimport definitions.
     */
    const FILENAME_DATAIMPORTS = 'dataimports.xml';
    
    /**
     * Holds the name of the file that contains our datasource definitions.
     */
    const FILENAME_DATASOURCES = 'datasources.xml';
    
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    
    // ---------------------------------- <IImportFactoryConfig IMPL> ----------------------------
    
    /**
     * Return the factory config for a data-import instance by name.
     * 
     * @param       string $name
     * 
     * @return      array
     * 
     * @see         IImportFactoryConfig::getDataImportConfig()
     */
    public function getDataImportConfig($name)
    {
        /* @var $importFactoryConfig DataImportsFactoryConfig */
        $importFactoryConfig = $this->getSetting(self::CFG_IMPORTS_CONFIG);
        
        $dataImportConfigs = $importFactoryConfig->getSetting(
            DataImportsFactoryConfig::CFG_DATAIMPORTS
        );
        
        if (!isset($dataImportConfigs[$name]))
        {
            throw new ImportFactoryException(
                "The given dataimport '" . $name . "' is not configured."
            );
        }
        
        return $dataImportConfigs[$name];
    }
    
    /**
     * Return the factory config for a data-source instance by name.
     * 
     * @param       string $name
     * 
     * @return      array
     * 
     * @see         IImportFactoryConfig::getDataSourceConfig()
     */
    public function getDataSourceConfig($name)
    {
        /* @var $importFactoryConfig DataImportsFactoryConfig */
        $sourceFactoryConfig = $this->getSetting(self::CFG_SOURCES_CONFIG);
        
        $dataSourceConfigs = $sourceFactoryConfig->getSetting(
            DataSourcesFactoryConfig::CFG_DATASOURCES
        );
        
        if (!isset($dataSourceConfigs[$name]))
        {
            throw new ImportFactoryException(
                "The given datasource '" . $name . "' is not configured."
            );
        }
        
        return $dataSourceConfigs[$name];
    }
    
    // ---------------------------------- </IImportFactoryConfig IMPL> ---------------------------
    
    
    // ---------------------------------- <ImportBaseConfig IMPL> --------------------------------
    
    /**
     * Load the given $configSource and return an array representation.
     *
     * @return      array
     *
     * @throws      ImportConfigException
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function load($configSrc) // @codingStandardsIgnoreEnd
    {
        if (!is_dir($configSrc))
        {
            throw new ImportFactoryException(
                "Can not create ImportFactoryConfig without a valid config path."
            );
        }
        
        $configDir = $configSrc;
        
        $importsFactoryConfig = new DataImportsFactoryConfig(
            $configDir . self::FILENAME_DATAIMPORTS
        );
        
        $sourcesFactoryConfig = new DataSourcesFactoryConfig(
            $configDir . self::FILENAME_DATASOURCES
        );
        
        return array(
            self::CFG_IMPORTS_CONFIG => $importsFactoryConfig,
            self::CFG_SOURCES_CONFIG => $sourcesFactoryConfig
        );
    }
    
    /**
     * Return an array with setting names, that we consider required.
     * 
     * @return      array
     * 
     * @see         ImportBaseConfig::getRequiredSettings()
     */
    public function getRequiredSettings()
    {
        return array(
            self::CFG_IMPORTS_CONFIG,
            self::CFG_SOURCES_CONFIG
        );
    }
    
    // ---------------------------------- </ImportBaseConfig IMPL> -------------------------------
}

?>