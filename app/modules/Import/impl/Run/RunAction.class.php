<?php

/**
 * The Import_RunAction class handles running imports with selectable datasources.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Run
 */
class Import_RunAction extends ImportBaseAction
{
    /**
     * Holds the name of our data_import parameter.
     */
    const PARAM_DATA_IMPORT = 'data_import';
    
    /**
     * Holds the name of our data_source parameter.
     */
    const PARAM_DATA_SOURCE = 'data_sources';
    
    /**
     * Execute the write logic for this action, hence run the import.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $import = $parameters->getParameter(self::PARAM_DATA_IMPORT);
        $dataSources = $parameters->getParameter(self::PARAM_DATA_SOURCE, array());
        $view = 'Success';
        
        foreach ($dataSources as $dataSource)
        {
            if (!$import->run($dataSource))
            {
                $view = 'Error';
            }
        }

        return $view;
    }
}

?>