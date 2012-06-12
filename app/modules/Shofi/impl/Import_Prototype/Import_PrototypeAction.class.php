<?php

/**
 * The Shofi_Import_PrototypeAction class is responseable for receiving shofi-prototype notifications
 * and translating them into the correct import execution.
 *
 * @version         $Id: Import_ImperiaAction.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Import_PrototypeAction extends ShofiBaseAction
{
    /**
     * Name of our shofi import definition.
     */
    const DATAIMPORT_NAME = 'shofi';

    /**
     * Name of our wkg data source definition.
     */
    const DATASOURCE_NAME = 'prototype';

    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        // $this->logInfo("Incoming Shofi::Place => " . PHP_EOL . print_r($parameters->getParameter('Data'), TRUE));

        try
        {
            $importFactory = ImportFactory::create();
            $dataImport = $importFactory->createDataImport(self::DATAIMPORT_NAME);
            $report = $dataImport->run($importFactory->createDataSource(
                self::DATASOURCE_NAME,
                array(
                    ArrayDataSourceConfig::CFG_DATA => $parameters->getParameter('Data')
                )
            ));

            if ($report->hasErrors())
            {
                $errors = array();
                foreach ($report->getErrors() as $error)
                {
                    $errors[] = $error['message'];
                }
                $this->setAttribute('errors', $errors);
                $this->logError("An unexpected error occured during import: " . print_r($report->getErrors(), TRUE));
                return 'Error';
            }

            return 'Success';
        }
        catch (Exception $e)
        {
            $this->setAttribute('errors', array($e->getMessage()));
            $this->logError("An unexpected error occured during import: " . $e->getMessage());
            return 'Error';
        }
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

        error_log(print_r($errors, TRUE));

        return 'Error';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>