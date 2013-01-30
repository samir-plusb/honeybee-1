<?php

/**
 * The Import_RunAction class handles running imports with selectable datasources.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
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
        $dataImport = $parameters->getParameter(self::PARAM_DATA_IMPORT);
        $dataSources = $parameters->getParameter(self::PARAM_DATA_SOURCE, array());
        $view = 'Success';

        foreach ($dataSources as $dataSource)
        {
            try
            {
                $report = $dataImport->run($dataSource);

                if ($report->hasErrors())
                {
                    $errors = array();
                    foreach ($report->getErrors() as $error)
                    {
                        $errors[] = $error['message'];
                    }
                    $this->setAttribute('errors', array(
                        "An unexpected error occured during import: " . print_r($report->getErrors(), TRUE)
                    ));
                    $this->logError("An unexpected error occured during import: " . print_r($report->getErrors(), TRUE));
                    return 'Error';
                }
            }
            catch(Exception $e)
            {
                throw $e;
                /* @todo better exception handling */
                $this->setAttribute('errors', array($e->getMessage()));
                $this->logError("An unexpected error occured during import: " . $e->getMessage());
                $view = 'Error';
            }
        }
        return $view;
    }

    public function handleWriteError(AgaviRequestDataHolder $parameters)
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $error)
        {
            $errors[] = $error['message'];
        }
        $this->setAttribute('errors', $errors);
        return 'Error';
    }
}

?>
