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

    protected $processedImportIds = array();

    protected $previousImportIds = array();

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

        $this->previousImportIds = $this->workflowService->findAllImportIdentifiers($dataSource);
    }

    protected function getWorkflowService()
    {
        return ShofiWorkflowService::getInstance();
    }

    protected function processRecord()
    {
        $record = $this->getCurrentRecord();

        $this->processedImportIds[] = $record->getImportIdentifier();
        $importContext = $this->determineRecordContext($record);
        $workflowItem = $importContext['item'];

        if ('create' === $importContext['action'])
        {
            // do the create thingy.
            $detailItem = $workflowItem->getDetailItem();
            // If we have a category mapping, apply it!
            $categories = $this->categoryMatcher->getMatchesFor($record->getCategorySource());
            if (! empty($categories))
            {
                $detailItem->setCategory(array_shift($categories));
                $detailItem->setAdditionalCategories($categories);
            }
            $workflowItem->setAttribute('data_status', 'import_created');
            if ($record instanceof BtkHotelDataRecord)
            {
                $workflowItem->setAttribute('isHidden', TRUE);
            }
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
                if ($this->mayOverride($workflowItem))
                {
                    echo "OVERRIDING EXISITING PLACE WITH GDOC/BTK DATA." . PHP_EOL;
                    $detailItem->applyValues($importData['detailItem']);
                }
                else
                {
                    $detailItem->softUpdate($importData['detailItem']);
                }
            }
            $workflowItem->setAttribute('data_status', 'import_updated');
            if ($record instanceof GdocHotelDataRecord)
            {
                $workflowItem->setAttribute('isHidden', FALSE);
            }
        }

        // ------ APPLY CATEGORIES - factor out to a addCategories method later.
        $sourceCategory = $record->getCategorySource();
        $categories = $this->categoryMatcher->getMatchesFor($sourceCategory);
        if (! empty($categories))
        {
            $workflowItem->getDetailItem()->setCategory(array_shift($categories));
            $workflowItem->getDetailItem()->setAdditionalCategories($categories);
        }

        // remember that we have been created by the current import-identifier. 
         $this->registerImportIdentifier($workflowItem, $record->getImportIdentifier());
        // persist the item's new state and add report incidents.
        $this->workflowService->storeWorkflowItem($workflowItem);
        if (isset($importContext['match']))
        {
            $this->registerMatch($workflowItem, $importContext['match']['item']);
        }
        if ('create' === $importContext['action'])
        {
            $this->shofiReport->onItemCreated($workflowItem);
            echo "--------------\n";
        }
        else
        {
            $this->shofiReport->onItemUpdated($workflowItem);
            echo "--------------\n";
        }
        // @todo introduce workflow events and relocate the export call to an event handler for created/updated/...
        //$this->export($workflowItem);
        return TRUE;
    }

    protected function determineRecordContext(ShofiDataRecord $record)
    {
        $action = 'create';
        $match = NULL;
        // look for a dataset that has been created by the same origin (import-identifier)
        $workflowItem = $this->workflowService->findItemByImportIdentifier($record->getImportIdentifier());
        if (! $workflowItem)
        {
            // no existing item for the given import-id, let's match!
            $workflowItem = $this->createMatchingSourceItem($record);
            $category = $workflowItem->getDetailItem()->getCategory();
            $match = $category ? $this->placeMatcher->matchClosest($workflowItem) : NULL;
            if ($match)
            {
                $matchedItem = $match['item'];
                $this->shofiReport->addIncident(
                    sprintf(
                        "An incoming place called '%s' matched against an existing item '%s', id: %s, with a distance of %s meters.",
                        $workflowItem->getCoreItem()->getName(),
                        $matchedItem->getCoreItem()->getName(),
                        $matchedItem->getIdentifier(),
                        $match['distance']
                    ),
                    ShofiDataImportReport::DEBUG
                );
                $action = 'update';
                $workflowItem = $matchedItem;
            }
        }
        else
        {
            $action = 'update';
            $this->shofiReport->addIncident(
                sprintf(
                    "Found existing place by last-import-id: '%s' with the name %s", 
                    $record->getImportIdentifier(),
                    $workflowItem->getCoreItem()->getName()
                ),
                ShofiDataImportReport::DEBUG
            );
        }

        return array('action' => $action, 'item' => $workflowItem, 'match' => $match);
    }

    protected function createMatchingSourceItem(ShofiDataRecord $record)
    {
        $sourceCategory = $record->getCategorySource();
        $categories = $this->categoryMatcher->getMatchesFor($sourceCategory);

        $shofiItem = $this->createNewShofiItem($record);
        $this->workflowService->localizeItem($shofiItem, TRUE);
        if (NULL === $categories)
        {
            $this->categoryMatcher->registerExternalCategory($sourceCategory);
            $this->shofiReport->onSourceCategoryRegistered($sourceCategory);
        }
        else if (empty($categories))
        {
            $this->shofiReport->onSourceCategoryUnmapped($sourceCategory);
        }
        else
        {
            $shofiItem->getDetailItem()->setCategory(array_shift($categories));
            $shofiItem->getDetailItem()->setAdditionalCategories($categories);
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

    protected function registerMatch(ShofiWorkflowItem $incomingItem, ShofiWorkflowItem $matchedItem)
    {
        $duplicatesGroup = $matchedItem->getAttribute('duplicates_group');
        if (! $duplicatesGroup)
        {
            $duplicatesGroup = array(
                'dups' => array($matchedItem->getIdentifier(), $incomingItem->getIdentifier()),
                'id' => $matchedItem->getIdentifier()
            );
        }
        else
        {
            $duplicatesGroup['dups'][] = $incomingItem->getIdentifier();
        }
        foreach ($duplicatesGroup['dups'] as $dupId)
        {
            $duplicate = $this->workflowService->fetchWorkflowItemById($dupId);
            $duplicate->setAttribute('duplicates_group', $duplicatesGroup);
            $this->workflowService->storeWorkflowItem($duplicate);
        }
    }

    protected function mayOverride(ShofiWorkflowItem $shofiItem)
    {
        static $overrideMappings = array(
            'places-wkg' => array('hotels-gdoc', 'hotels-btk'),
            'hotels-btk' => array('hotels-gdoc')
        );
        $primarySource = $shofiItem->getMasterRecord()->getSource();
        $overrideWhitelist = isset($overrideMappings[$primarySource]) ? $overrideMappings[$primarySource] : array();

        if (in_array($this->getDataSource()->getName(), $overrideWhitelist))
        {
            $salesItem = $shofiItem->getSalesItem();
            $product = $salesItem->getProduct();
            return ('premium' !== $product || 'business' !== $product);
        }
        return FALSE;
    }

    protected function registerImportIdentifier(ShofiWorkflowItem $shofiItem, $importIdentifier)
    {
        $previousImportIds = $shofiItem->getAttribute('import_ids', array());
        if (! in_array($importIdentifier, $previousImportIds))
        {
            $previousImportIds[] = $importIdentifier;
        }
        else
        {
            // should not happen, 
            // we are trying to re-register an import identifier, that we require to be unique.
        }
        $shofiItem->setAttribute('import_ids', $previousImportIds);
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
        $this->sendImportReport();

        parent::cleanup();
    }

    protected function sendImportReport()
    {
        $dataSource = $this->getDataSource();
        $transManager = AgaviContext::getInstance()->getTranslationManager();
        $routing = AgaviContext::getInstance()->getRouting();
        $dataSourceLabel = $transManager->_($dataSource->getName(), 'shofi.list');
        $reportSubject = sprintf(
            "Shofi %s Importbericht, Datum/Zeit: %s/%s", 
            $dataSourceLabel, date('d-m-Y'), date('H:i')
        );
        $reportLines = array(
            sprintf("Hi,\nder Shofi-Import für die Quelle %s wurde so eben erfolgreich beendet.", $dataSourceLabel),
            "Anbei eine Übersicht zu ausgewählten Infos:\n",
            sprintf("- es wurden %s neue Orte angelegt.", $this->shofiReport->getItemsCreatedCount()),
            sprintf("- es wurden %s bestehende Orte aktualisiert.", $this->shofiReport->getItemsUpdatedCount())
        );
        if ($dataSource instanceof BtkHotelDataSource)
        {
            $deletedOldImportIds = array();
            foreach ($this->previousImportIds as $previousImportId)
            {
                if (! in_array($previousImportId, $this->processedImportIds))
                {
                    $deletedOldImportIds[] = $previousImportId;
                }
            }
            $createdNewImportIds = array();
            foreach ($this->processedImportIds as $processedImportId)
            {
                if (! in_array($processedImportId, $this->previousImportIds))
                {
                    $createdNewImportIds[] = $processedImportId;
                }
            }
            $reportLines[] = "\nDa es sich um einen BTK-Import handelt, noch ein paar extra Infos:";
            $reportLines[] = sprintf("Es wurden insgesamt %d neue %s Orte erstellt.", 
                count($createdNewImportIds), $dataSourceLabel);
            if (0 < count($createdNewImportIds))
            {
                $reportLines[] = sprintf("Es folgt eine Liste mit jeweils Name und Link eines neu erstellten %s Eintrags:",
                    $dataSourceLabel);
                foreach ($createdNewImportIds as $newId)
                {
                    $item = $this->workflowService->findItemByImportIdentifier($newId);
                    if (! $item)
                    {
                        echo "Import-Id (create) lookup failed while generating report: " . $newId;
                        continue;
                    }
                    $reportLines[] = sprintf("%s, %s",
                        $item->getCoreItem()->getName(),
                        $routing->gen('workflow.run', array('ticket' => $item->getTicketId(), 'type' => 'shofi'))
                    );
                }
            }
            $reportLines[] = sprintf("Es wurden insgesamt %d bereits importierte %s Orte nicht mehr geliefert.", 
                count($deletedOldImportIds), $dataSourceLabel);
            if (0 < count($deletedOldImportIds))
            {
                $reportLines[] = sprintf("Hier eine Liste mit den jeweils nicht mehr gelieferten %s Einträgen:",
                    $dataSourceLabel);
                foreach ($deletedOldImportIds as $deletedId)
                {
                    $item = $this->workflowService->findItemByImportIdentifier($deletedId);
                    if (! $item)
                    {
                        echo "Import-Id (delete) lookup failed while generating report: " . $deletedId;
                        continue;
                    }
                    $reportLines[] = sprintf("%s, %s",
                        $item->getCoreItem()->getName(),
                        $routing->gen('workflow.run', array('ticket' => $item->getTicketId(), 'type' => 'shofi'))
                    );
                }
            }
        }
        $reportLines[] = "\nMidas wünscht noch einen guten Tag.\n";
        $reportMessage = implode(PHP_EOL, $reportLines);
        $mailTo = 'thorsten.schmitt-rink@berlinonline.de jens.bahr.berlinonline.de';
        mail($mailTo, $reportSubject, $reportMessage);
        echo sprintf("Mail to:\n%s\n----\nSubject:\n%s\n----\nMessage:\n%s\n----\n", 
            $mailTo, $reportSubject, $reportMessage);
    }
}
