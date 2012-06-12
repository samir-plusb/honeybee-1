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
class Shofi_Import_PrototypeDeleteAction extends ShofiBaseAction
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
        $type = $parameters->getParameter('type');
        $identifier = $parameters->getParameter('id');
        $cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );

        if ('place' === $type)
        {
            $service = ShofiWorkflowService::getInstance();
            $service->deleteWorkflowItem(
                $service->fetchWorkflowItemById($identifier)
            );
            $cmExport->deleteEntity($identifier, $type);
        }
        else
        {
            $service = ShofiCategoriesWorkflowService::getInstance();
            $service->deleteWorkflowItem(
                $service->fetchWorkflowItemById($identifier)
            );
            $cmExport->deleteEntity($identifier, $type);
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