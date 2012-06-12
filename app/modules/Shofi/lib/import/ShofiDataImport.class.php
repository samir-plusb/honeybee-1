<?php

/**
 * The ShofiDataImport is responseable for importing shofi data to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import
 */
class ShofiDataImport extends WorkflowItemDataImport
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
        return ShofiWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        $record = $this->getCurrentRecord();
        $importData = $record->toArray();
        unset ($importData[BaseDataRecord::PROP_IDENT]);
        // prepare workflow item data.
        $itemData = array();
        if (isset($importData['detailItem']))
        {
            $itemData['detailItem'] = $importData['detailItem'];
            unset($importData['detailItem']);
        }
        if (isset($importData['coreItem']))
        {
            $itemData['coreItem'] = $importData['coreItem'];
            unset($importData['coreItem']);
        }
        if (isset($importData['salesItem']))
        {
            $itemData['salesItem'] = $importData['salesItem'];
            unset($importData['salesItem']);
        }
        if (isset($importData['mongoId']))
        {
            $itemData['attributes'] = array(
                'mongoId' => $importData['mongoId']
            );
            unset($importData['mongoId']);
        }

        $workflowItem = $this->workflowService->fetchWorkflowItemById($record->getIdentifier());
        if (! $workflowItem)
        {
            $workflowItem = $this->createWorkflowItem($record->getIdentifier(), $importData, $itemData);
        }
        else
        {
            $this->updateWorkflowItem($workflowItem, $importData, $itemData);
        }

        // Check if contentmachine export is enabled and all required settings are.
        $exportAllowed = AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED)
            && AgaviConfig::has(ContentMachineHttpExport::SETTING_EXPORT_URL);
        if ($exportAllowed)
        {
            // @todo we need a try catch here,
            // so the import does not break just because the conentmachine is not reachable.
            $this->cmExport->exportShofiPlace($workflowItem);
        }
        return TRUE;
    }
}

?>
