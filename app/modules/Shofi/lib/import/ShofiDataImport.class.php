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

    protected $categoryMatcher;

    protected $placeMatcher;

    protected $shofiReport;

    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);

        $this->cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );
        $this->categoryMatcher = new ShofiCategoryMatcher(
            AgaviContext::getInstance()->getDatabaseConnection('Shofi.Write')
        );
        $this->placeMatcher = new ShofiPlaceMatcher();
        $this->shofiReport = new ShofiDataImportReport(TRUE);
    }

    protected function getWorkflowService()
    {
        return ShofiWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        $record = $this->getCurrentRecord();
        // look for a dataset that has been created by the same origin (import-identifier)
        $workflowItem = $this->workflowService->findItemByImportIdentifier($record->getImportIdentifier());
        if (! $workflowItem)
        {
            // no existing item for the given import-id, let's match!
            if (($matchedItem = $this->findMatchingPlace($record)))
            {
                $workflowItem = $matchedItem['item'];
                $this->shofiReport->addIncident(
                    sprintf(
                        "Matched item '%s', id: %s with a distance of %s",
                        $workflowItem->getCoreItem()->getName(),
                        $workflowItem->getIdentifier(),
                        $matchedItem['distance']
                    ),
                    ShofiDataImportReport::DEBUG
                );
            }
        }
        else
        {
            $this->shofiReport->addIncident(
                sprintf(
                    "Found existing place by last-import-id: '%s' with the name %s\n", 
                    $record->getImportIdentifier(),
                    $workflowItem->getCoreItem()->getName()
                ),
                ShofiDataImportReport::DEBUG
            );
        }

        $action = 'updated';
        // no item found, neither by import-identifier nor by geo-title matching
        if (! $workflowItem)
        {
            // do the create thingy.
            $workflowItem = $this->createNewShofiItem($record);
            $this->workflowService->localizeItem($workflowItem, TRUE);
            $detailItem = $workflowItem->getDetailItem();
            // If we have a category mapping, apply it!
            $categories = $this->categoryMatcher->getMatchesFor($record->getCategorySource());
            if (! empty($categories))
            {
                $detailItem->setCategory(array_shift($categories));
                $detailItem->setAdditionalCategories($categories);
            }
            $workflowItem->setAttribute('data_status', 'import_created');
            $action = 'created';
        }
        else
        {
            // do the update thingy.
            $importData = $record->toArray();
            unset ($importData[BaseDataRecord::PROP_IDENT]);
            $this->updateWorkflowItem($workflowItem, $importData);
            if (isset($importData['detailItem']))
            {
                $detailItem = $workflowItem->getDetailItem();
                $detailItem->softUpdate($importData['detailItem']);
            }
            $workflowItem->setAttribute('data_status', 'import_updated');
        }
        // remember that we have been created by the current import-identifier. 
         $this->registerImportIdentifier($workflowItem, $record->getImportIdentifier());
        // persist the item's new state and add report incidents.
        $this->workflowService->storeWorkflowItem($workflowItem);
        if ('updated' === $action)
        {
            $this->shofiReport->onItemUpdated($workflowItem);
        }
        else
        {
            $this->shofiReport->onItemCreated($workflowItem);
        }
        // @todo introduce workflow events and relocate the export call to an event handler for created/updated/...
        //$this->export($workflowItem);
        return TRUE;
    }

    protected function registerImportIdentifier(ShofiWorkflowItem $shofiItem, $importIdentifier)
    {
        $currentImportIds = $shofiItem->getAttribute('import_ids', array());
        if (! in_array($importIdentifier, $currentImportIds))
        {
            $currentImportIds[] = $importIdentifier;
        }
        else
        {
            // should not happen, 
            // we are trying to re-register an import identifier, that we require to be unique.
        }
        $shofiItem->setAttribute('import_ids', $currentImportIds);
    }

    protected function findMatchingPlace(ShofiDataRecord $record)
    {
        $matchedPlace = NULL;
        $matchingSourceItem = $this->createMatchingSourceItem($record);
        if ($matchingSourceItem)
        {
            $matchedPlace = $this->placeMatcher->match($matchingSourceItem);
        }
        
        return $matchedPlace;
    }

    protected function createMatchingSourceItem(ShofiDataRecord $record)
    {
        $shofiItem = $this->createNewShofiItem($record);
        $sourceCategory = $record->getCategorySource();
        $categories = $this->categoryMatcher->getMatchesFor($sourceCategory);
        if (NULL === $categories)
        {
            $this->categoryMatcher->registerExternalCategory($sourceCategory);
            $this->shofiReport->onSourceCategoryRegistered($sourceCategory);
        }
        else if (! empty($categories))
        {
            $shofiItem->getDetailItem()->setCategory(array_shift($categories));
            $shofiItem->getDetailItem()->setAdditionalCategories($categories);
        }
        else
        {
            $this->shofiReport->onSourceCategoryUnmapped($sourceCategory);
            $shofiItem = NULL;
        }

        return $shofiItem;
    }

    protected function createNewShofiItem(ShofiDataRecord $record)
    {
        $record = $this->getCurrentRecord();
        $importData = $record->toArray();
        unset ($importData[BaseDataRecord::PROP_IDENT]);

        $itemData = array();
        if (isset($importData['attributes']))
        {
            $itemData['attributes'] = $importData['attributes'];
            unset($importData['attributes']);
        }

        $copyProps = array('coreItem', 'detailItem', 'salesItem');
        foreach($copyProps as $prop)
        {
            if (isset($importData[$prop]))
            {
                $itemData[$prop] = $importData[$prop];
            }
        }
        $newItem = $this->workflowService->createWorkflowItem($itemData);
        $newItem->setMasterRecord($newItem->createMasterRecord($importData));

        return $newItem;
    }

    protected function export(ShofiWorkflowItem $workflowItem)
    {
        $transManager = AgaviContext::getInstance()->getTranslationManager();
        if (AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED, FALSE))
        {
            if (! $this->cmExport->exportShofiPlace($workflowItem))
            {
                if ($this->cmExport->hasErrors())
                {
                    $errors = array_merge($errors, $this->cmExport->getLastErrors());
                }
                else
                {
                    $errors[] = $transManager->_('export_error', 'shofi.errors');
                }
            }
        }
        
        $keywords = $workflowItem->getDetailItem()->getKeywords();
        if (in_array('Kino', $keywords))
        {
            $boFrontendMovieExport = new MoviesFrontendExport();
            $boFrontendMovieExport->exportTheater($workflowItem);
        }

        $tipFrontendLocationExport = new TipFrontendLocationExport();
        $tipFrontendLocationExport->export($workflowItem);
    }

    protected function cleanup()
    {
        $newSourceCategories = $this->shofiReport->getNewSourceCategories();
        echo sprintf("Created %s number of new shofi items.\n", $this->shofiReport->getItemsCreatedCount());
        echo sprintf("Updated %s number of existing shofi items.\n", $this->shofiReport->getItemsUpdatedCount());
        echo sprintf("Registered %s number of new source-categories to the category-matching table.\n", count($newSourceCategories));
        echo "During import the following errors occured:\n";
        $warnings = $this->shofiReport->getIncidents(ShofiDataImportReport::WARN);
        echo "Encountered " . count($warnings) . " number of warnings.\n";

        parent::cleanup();
    }
}
