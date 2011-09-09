<?php

/**
 * The Import_ImperiaAction class handles dispatch imperia imports.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
class Import_ImperiaAction extends ImportBaseAction
{
    /**
     * Holds the name of the our default import config file.
     */
    const DEFAULT_CONFIG_FILE = 'polizeimeldungen.xml';
    
    /**
     * Holds the name of our config parameter.
     */
    const PARAM_CONFIG_NAME = 'config';
    
    /**
     * Execute the write logic for this action, hence run the import.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $importFactory = new DataImportFactory(
            $parameters->getParameter(self::PARAM_CONFIG_NAME, $this->getImportConfigDirectory())
        );

        $import = $importFactory->createDataImport('CouchDbDataImportConfig');
        $dataSource = $importFactory->createDataSource('ImperiaDataSourceConfig');

        if (!$import->run($dataSource))
        {
            return 'Error';
        }

        return 'Success';
    }
    
    /**
     * Return a path pointing to our config directory.
     * 
     * @return      string 
     */
    private function getImportConfigDirectory()
    {
        return AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR .
            'modules' . DIRECTORY_SEPARATOR .
            'Import' . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            'imports' . DIRECTORY_SEPARATOR .
            self::DEFAULT_CONFIG_FILE;
    }
}

?>