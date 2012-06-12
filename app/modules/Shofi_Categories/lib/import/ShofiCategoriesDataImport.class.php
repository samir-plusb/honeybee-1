<?php

/**
 * The ShofiCategoriesDataImport is responseable for importing shofi data to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi_Categories
 * @subpackage      Import
 */
class ShofiCategoriesDataImport extends WorkflowItemDataImport
{
    protected $cmExport;

    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);

        $this->cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );
    }

    protected function getWorkflowService()
    {
        return ShofiCategoriesWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        if (parent::processRecord())
        {
            $record = $this->getCurrentRecord();
            $workflowItem = $this->workflowService->fetchWorkflowItemById($record->getIdentifier());

            // Check if contentmachine export is enabled and all required settings are.
            $exportAllowed = AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED)
                && AgaviConfig::has(ContentMachineHttpExport::SETTING_EXPORT_URL);
            if ($exportAllowed)
            {
                // @todo we need a try catch here,
                // so the import does not break just because the conentmachine is not reachable.
                $this->cmExport->exportShofiCategory($workflowItem);
            }
            return TRUE;
        }
        return FASE;
    }
}

?>
