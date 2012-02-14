<?php

/**
 * The Import_TriggerMailAction class is responseable for receiving procmail notifications
 * (with rawmail data from stdin) and translating them into the correct import execution.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerMailAction extends ImportBaseAction
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
        try
        {
            $rawMail = $parameters->getParameter(ImportMailValidator::DEFAULT_PARAM_EXPORT);
            $importFactory = $this->createImportFactory();
            $dataImport = $importFactory->createDataImport(self::DATAIMPORT_WORKFLOW);
            $dataSources = array(
                $importFactory->createDataSource(
                    self::DATASOURCE_PROCMAIL,
                    array(
                        ArrayDataSourceConfig::CFG_DATA => array($rawMail)
                    )
                )
            );

            return $this->runImports($dataImport, $dataSources);
        }
        catch (Exception $e)
        {
            $this->setAttribute('errors', array($e->getMessage()));
            $this->logError("An unexpected error occured during import: " . $e->getMessage());
        }

        return 'Error';
    }

    /**
     * Handle write errors, hence failed validation on the incoming data.
     *
     * @param AgaviRequestDataHolder $parameters
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $error)
        {
            $errors[] = $error['message'];
        }
        $this->setAttribute('errors', $errors);
        $this->logError("Mail import validation error(s): " . implode(', ' . PHP_EOL, $errors));

        return 'Error';
    }
}

?>