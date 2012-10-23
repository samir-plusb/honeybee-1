<?php

/**
 * The MoviesDataImport is responseable for importing movie data to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import
 */
class MoviesDataImport extends WorkflowItemDataImport
{
    protected $idSequence;

    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);
        $this->idSequence = new ProjectIdSequence('movies');
    }

    protected function getWorkflowService()
    {
        return MoviesWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        $record = $this->getCurrentRecord();
        $importData = $record->toArray();
        unset ($importData[BaseDataRecord::PROP_IDENT]);

        $workflowItem = $this->workflowService->findItemByImportIdentifier($record->getImportIdentifier());
        echo "Import Id: " . $record->getImportIdentifier() . PHP_EOL;
        if ($workflowItem instanceof MoviesWorkflowItem)
        {
            $this->updateWorkflowItem($workflowItem, $importData);
        }
        else
        {
            $workflowItem = $this->createWorkflowItem(NULL, $importData);
            $workflowItem->setExportId(
                $this->idSequence->nextId($workflowItem->getIdentifier())
            );
        }

        // remember that we have been created by the current import-identifier. 
        $this->registerImportIdentifier($workflowItem, $record->getImportIdentifier());
        $this->workflowService->storeWorkflowItem($workflowItem);
    }

    protected function registerImportIdentifier(MoviesWorkflowItem $movieItem, $importIdentifier)
    {
        $previousImportIds = $movieItem->getAttribute('import_ids', array());
        if (! in_array($importIdentifier, $previousImportIds))
        {
            $previousImportIds[] = $importIdentifier;
        }
        else
        {
            // should not happen, 
            // we are trying to re-register an import identifier, that we require to be unique.
        }
        $movieItem->setAttribute('import_ids', $previousImportIds);
    }
}
