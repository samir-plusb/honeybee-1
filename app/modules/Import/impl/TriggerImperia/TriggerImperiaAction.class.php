<?php

/**
 * The Import_TriggerImperiaAction class is responseable for receiving imperia notifications
 * and translating them into the correct import execution.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerImperiaAction extends ImportBaseAction
{
    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $docIds = $parameters->getParameter(
            ImperiaJsonValidator::DEFAULT_PARAM_EXPORT,
            array()
        );

        $importFactory = $this->createImportFactory();
        $dataImport = $importFactory->createDataImport(self::DATAIMPORT_COUCHDB);
        $dataSources = array(
            $importFactory->createDataSource(
                self::DATASOURCE_IMPERIA,
                array(
                    ImperiaDataSourceConfig::PARAM_DOCIDS => $docIds
                )
            )
        );

        return $this->runImports($dataImport, $dataSources);
    }
}

?>