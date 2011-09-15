<?php

/**
 * The Import_TriggerImperiaAction class is responseable for receiving imperia notifications
 * and translating them into the correct import execution.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerImperiaAction extends ImportBaseAction
{
    /**
     * Name of our couchdb data import definition.
     */
    const DATAIMPORT_COUCHDB = 'couchdb';
    
    /**
     * Name of our imperia data source definition.
     */
    const DATASOURCE_IMPERIA = 'imperia';
    
    /**
     * Execute the write logic for this action, hence run the import.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $factoryConfig = new ImportFactoryConfig(
            AgaviConfig::get('import.config_dir')
        );
        $importFactory = new ImportFactory($factoryConfig);
        
        $docIds = $parameters->getParameter(ImperiaJsonValidator::DEFAULT_PARAM_EXPORT, array());
        
        $import = $importFactory->createDataImport(self::DATAIMPORT_COUCHDB);
        $imperiaDataSource = $importFactory->createDataSource(
            self::DATASOURCE_IMPERIA,
            array(
                ImperiaDataSourceConfig::PARAM_DOCIDS => $docIds
            )
        );
        
        if ($import->run($imperiaDataSource))
        {
            return 'Success';
        }
        
        return 'Error';
    }
}

?>