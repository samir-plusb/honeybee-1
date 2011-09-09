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
            $parameters->getParameter(self::PARAM_CONFIG_NAME)
        );

        $import = $importFactory->createDataImport();
        $dataSource = $importFactory->createDataSource();

        if (!$import->run($dataSource))
        {
            return 'Error';
        }

        return 'Success';
    }
}

?>