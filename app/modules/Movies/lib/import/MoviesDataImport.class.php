<?php

/**
 * The MoviesDataImport is responseable for importing movie data to the domain's workflow.
 *
 * @version         $Id: MoviesDataImport.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import
 */
class MoviesDataImport extends WorkflowItemDataImport
{
    protected function getWorkflowService()
    {
        return MoviesWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        $record = $this->getCurrentRecord();
        $importData = $record->toArray();
        unset ($importData[BaseDataRecord::PROP_IDENT]);

        $workflowItem = $this->workflowService->fetchWorkflowItemById($record->getIdentifier());
        if (! $workflowItem)
        {
            if ($record instanceof MoviesXmlDataRecord)
            {
                $workflowItem = $this->createWorkflowItem($record->getIdentifier(), $importData);
            }
            else
            {
                echo("No workflow item foundduring screenings import for movie: ".$record->getIdentifier().PHP_EOL);
            }
        }
        else
        {
            $this->updateWorkflowItem($workflowItem, $importData);
        }
        if ($workflowItem)
        {
            $frontendExport = new MoviesFrontendExport();
            $frontendExport->exportMovie($workflowItem);
        }
    }
}

?>
