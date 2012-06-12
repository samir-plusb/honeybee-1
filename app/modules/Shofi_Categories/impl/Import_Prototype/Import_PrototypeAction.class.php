<?php

/**
 * The Shofi_Categories_Import_PrototypeAction class is responseable for receiving shofi-prototype notifications
 * and translating them into the correct category import execution.
 *
 * @version         $Id: Import_PrototypeAction.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 * @subpackage      Mvc
 */
class Shofi_Categories_Import_PrototypeAction extends ShofiCategoriesBaseAction
{
    /**
     * Name of our shofi import definition.
     */
    const DATAIMPORT_NAME = 'shofi.categories';

    /**
     * Name of our wkg data source definition.
     */
    const DATASOURCE_NAME = 'prototype.category';

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
            $importFactory = ImportFactory::create();
            $dataImport = $importFactory->createDataImport(self::DATAIMPORT_NAME);
            $report = $dataImport->run($importFactory->createDataSource(
                self::DATASOURCE_NAME,
                array(
                    ArrayDataSourceConfig::CFG_DATA => array($parameters->getParameters())
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
        }
        catch(Exception $e)
        {
            /* @todo better exception handling */
            $this->setAttribute('errors', array($e->getMessage()));
            $this->logError("An unexpected error occured during import: " . $e->getMessage());
            $view = 'Error';
        }

        return 'Success';
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
        return 'Error';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>