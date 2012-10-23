<?php

/**
 * The ImportConfigFileValidator class provides validation of import related config files
 * and checks for their existance and for schema violations.
 * Throws exceptions if invalid import configurations are encountered inside a development environment.
 * Reports casual import errors for all other environments.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Agavi/Validator
 */
class ImportValidator extends AgaviStringValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the config file that holds our dataimport definitions.
     */
    const CONFIG_FILE_IMPORTS = 'dataimports.xml';

    /**
     * Holds the name of the config file that holds our datasource definitions.
     */
    const CONFIG_FILE_SOURCES = 'datasources.xml';

    /**
     * Holds the name of our 'export_data_import' parameter.
     * This parameter defines the parameter name inside our request-data
     * at which our validated IDataImport object will be exported to.
     */
    const PARAM_EXPORT_IMPORT = 'export_data_import';

    /**
     * Holds the name of our 'export_data_source' parameter.
     * This parameter defines the parameter name inside our request-data
     * at which our validated IDataSource object will be exported to.
     */
    const PARAM_EXPORT_DATASRC = 'export_data_sources';

    /**
     * Holds the default value that is used when no custom self::PARAM_EXPORT_IMPORT has been defined
     * in our validation config.
     */
    const DEFAULT_EXPORT_IMPORT = 'data_import';

    /**
     * Holds the default value that is used when no custom self::PARAM_EXPORT_DATASRC has been defined
     * in our validation config.
     */
    const DEFAULT_EXPORT_SOURCE = 'data_sources';

    /**
     * Holds the name of the error thrown for invalid import or datasource configurations.
     */
    const ERR_INVALID_CONFIG = 'invalid_config';

    /**
     * Holds the name of the error thrown for invalid import arguments.
     */
    const ERR_INVALID_IMPORT = 'invalid_import';

    /**
     * Holds the name of the error thrown for invalid datasources arguments.
     */
    const ERR_INVALID_DATASOURCES = 'invalid_datasources';

    /**
     * Holds the name of the argument that specifies where to look for our import name.
     */
    const ARG_IMPORT = 'import';

    /**
     * Holds the name of the parameter,
     * that holds the name of our argument that specifies where to look for our datasource names.
     */
    const PARAM_ARG_DATASOURCES = 'arg_datasources';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <AgaviStringValidator OVERRIDES> -----------------------

    /**
     * Validates that their is a valid import config file for a provided config name.
     *
     * @return      boolean
     *
     * @see         AgaviStringValidator::validate()
     */
    protected function validate()
    {
        if (!parent::validate())
        {
            return FALSE;
        }

        $dataImport = NULL;
        $dataSources = NULL;

        $importName =& $this->getData($this->getArgument(self::ARG_IMPORT));
        $onlyTheseSources =& $this->getData($this->getParameter(self::PARAM_ARG_DATASOURCES, 'datasources'));

        try
        {
            $configDir = AgaviConfig::get('import.config_dir');
            $importFactoryConfig = new ImportFactoryConfig($configDir);
            $importFactory = new ImportFactory($importFactoryConfig);

            $dataImport = $importFactory->createDataImport($importName);
            $onlyThese = is_string($onlyTheseSources) ? explode(',', $onlyTheseSources) : array();

            $dataSources = $importFactory->createDataSourcesForImport($importName, $onlyThese);
        }
        catch (ImportFactoryException $e)
        {
            if (!$dataImport)
            {
                $this->throwError(self::ERR_INVALID_IMPORT);
                return FALSE;
            }
        }
        catch (Exception $e)
        {
            throw $e;
            $this->throwError(self::ERR_INVALID_CONFIG);
            return FALSE;
        }

        if (empty ($dataSources))
        {
            $this->throwError(self::ERR_INVALID_DATASOURCES);
            return FALSE;
        }

        if (!empty($onlyThese) && count($onlyThese) != count($dataSources))
        {
            $this->throwError(self::ERR_INVALID_DATASOURCES);
            return FALSE;
        }

        $this->exportArguments($dataImport, $dataSources);
        return TRUE;
    }

    // ---------------------------------- </AgaviStringValidator OVERRIDES> ----------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Export the given IDataImport and IDataSources to our request-data,
     * to have them available in the action.
     *
     * @param       IDataImport $dataImport
     * @param       array $dataSources
     */
    protected function exportArguments(IDataImport $dataImport, array $dataSources)
    {
        $exportImportName = $this->getParameter(
            self::PARAM_EXPORT_IMPORT,
            self::DEFAULT_EXPORT_IMPORT
        );

        $exportDataSources = $this->getParameter(
            self::PARAM_EXPORT_DATASRC,
            self::DEFAULT_EXPORT_SOURCE
        );

        $affectedArgs = array(
            $exportImportName => $dataImport,
            $exportDataSources => $dataSources
        );

        foreach ($affectedArgs as $argName => $argValue)
        {
            $this->export($argValue, $argName);
        }

        $this->setAffectedArguments(array_keys($affectedArgs));
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>