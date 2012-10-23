<?php

/**
 * The News_Import_ImperiaAction class is responseable for receiving imperia notifications
 * and translating them into the correct import execution.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Import_ImperiaAction extends NewsBaseAction
{
    /**
     * Name of our workflow import definition.
     */
    const DATAIMPORT_NAME = 'news';

    /**
     * Name of our imperia data source definition.
     */
    const DATASOURCE_NAME = 'imperia';

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
            $dataImport->run($importFactory->createDataSource(
                self::DATASOURCE_NAME,
                array(
                    ImperiaDataSourceConfig::CFG_DOCIDS => $parameters->getParameter(
                        ImperiaJsonValidator::DEFAULT_PARAM_EXPORT,
                        array()
                    )
                )
            ));
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
        $this->logError("Mail import validation error(s): " . implode(', ' . PHP_EOL, $errors));

        return 'Error';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>