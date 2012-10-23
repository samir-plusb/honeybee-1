<?php

/**
 * The EventsDataImport is responseable for importing movie data to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import
 */
class EventsDataImport extends WorkflowItemDataImport
{
    /**
     * Return the workflow service instance to use while importing.
     *
     * @return EventsWorkflowService
     */
    protected function getWorkflowService()
    {
        return EventsWorkflowService::getInstance();
    }

    /**
     * Process/import the data record for the current import loop.
     * After importing/updating the corresponding workflow-item is synced to FE database.
     */
    protected function processRecord()
    {
        $record = $this->getCurrentRecord();

        if (! ($record instanceof EventsDataRecord))
        {
            echo(
                "Only IDataRecords by the type of EventsDataRecord and descendants are supported here." . PHP_EOL .
                get_class($record) . " - record type given with id: " . $record->getIdentifier() . PHP_EOL
            );
            // @todo log dog
        }

        $importData = $record->toArray();
        unset ($importData[BaseDataRecord::PROP_IDENT]);

        $workflowItem = $this->workflowService->fetchWorkflowItemById($record->getIdentifier());
        if (! $workflowItem)
        {
            $workflowItem = $this->createWorkflowItem($record->getIdentifier(), $importData);
        }
        else
        {
            $this->updateWorkflowItem($workflowItem, $importData);
        }

        if ($workflowItem)
        {
            $frontendExport = new TipFrontendEventExport();
            $frontendExport->export($workflowItem);
        }
    }
}
