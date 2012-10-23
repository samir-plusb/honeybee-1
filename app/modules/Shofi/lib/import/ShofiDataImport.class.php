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

    protected $idSequence;

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
        $this->idSequence = new ProjectIdSequence('shofi');
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
                    echo "OVERRIDING EXISITING PLACE WITH GDOC DATA." . PHP_EOL;
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
        $workflowItem->setExportId(
            $this->idSequence->nextId($workflowItem->getIdentifier())
        );
        $this->workflowService->storeWorkflowItem($workflowItem);
        if (isset($importContext['match']) && 'create' === $importContext['action'])
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
        $this->export($workflowItem);
        return TRUE;
    }

    protected function determineRecordContext(ShofiDataRecord $record)
    {
        $action = 'create';
        $match = NULL;
        $sourceItem = $this->createMatchingSourceItem($record);
        // look for a dataset that has been created by the same origin (import-identifier)
        $workflowItem = $this->workflowService->findItemByImportIdentifier($record->getImportIdentifier());
        if (! $workflowItem)
        {
            // no existing item for the given import-id, let's match!
            $category = $sourceItem->getDetailItem()->getCategory();
            $match = $category ? $this->placeMatcher->matchClosest($sourceItem) : NULL;
            if ($match)
            {
                $matchedItem = $match['item'];
                $this->shofiReport->onItemMatched($sourceItem, $matchedItem, $match['distance']);
                if (TRUE === $match['exactly_same'])
                {
                    $action = 'update';
                    $workflowItem = $matchedItem;

                    $this->shofiReport->addIncident(
                        sprintf("Found an exact match for place: %s", $workflowItem->getCoreItem()->getName()),
                        ShofiDataImportReport::DEBUG
                    );
                }
            }
            $workflowItem = $workflowItem ? $workflowItem : $sourceItem;
        }
        else
        {
            $action = 'update';

            $this->shofiReport->addIncident(
                sprintf(
                    "Found existing place '%s' by last-import-id: %s for an incoming place with name '%s'.",
                    $workflowItem->getCoreItem()->getName(),
                    $record->getImportIdentifier(),
                    $sourceItem->getCoreItem()->getName()
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
                'gid' => $matchedItem->getIdentifier()
            );
        }
        else if(! in_array($incomingItem->getIdentifier(), $duplicatesGroup['dups']))
        {
            $duplicatesGroup['dups'][] = $incomingItem->getIdentifier();
        }

        foreach ($duplicatesGroup['dups'] as $dupId)
        {
            $curGroup = array(
                'dups' => $duplicatesGroup['dups'],
                'gid' => $duplicatesGroup['gid']
            );
            if ($duplicatesGroup['gid'] === $dupId)
            {
                $curGroup['group_leader'] = TRUE;
            }
            $duplicate = $this->workflowService->fetchWorkflowItemById($dupId);
            $duplicate->setAttribute('duplicates_group', $curGroup);
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
        $dataSourceLabel = $transManager->_($dataSource->getName(), 'shofi.list');
        $reportSubject = sprintf(
            "Shofi %s Importbericht, Datum/Zeit: %s/%s", 
            $dataSourceLabel, date('d-m-Y'), date('H:i')
        );
        $reportLines = array(
            sprintf("Hallo,\n\nder Shofi-Import für die Quelle '%s' wurde so eben erfolgreich beendet.", $dataSourceLabel),
            "Anbei eine Übersicht zu ausgewählten Infos:\n",
            sprintf("- Es wurden %s neue Orte angelegt.", $this->shofiReport->getItemsCreatedCount()),
            sprintf("- Es wurden %s bestehende Orte aktualisiert.", $this->shofiReport->getItemsUpdatedCount()),
            sprintf("- %d Orte wurden positiv mit einem existierenden Ort 'gematched'.", $this->shofiReport->getItemsMatchedCount())
        );
        if ($dataSource instanceof BtkHotelDataSource)
        {
            $urlTemplate = sprintf("%s/workflow/run?type=shofi&ticket={:TICKET:}", ProjectEnvironmentConfig::getBaseHref());
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
            $reportLines[] = sprintf("\nDa es sich um einen '%s' Import handelte, noch ein paar extra Infos:", $dataSourceLabel);
            $reportLines[] = sprintf("- Es wurden insgesamt %d Orte erstmalig geliefert.", count($createdNewImportIds));
            $reportLines[] = sprintf("- %d vormals zur Verfügung gestellte Orte waren nun nicht mehr dabei.", count($deletedOldImportIds));
            if (0 < count($createdNewImportIds))
            {
                $reportLines[] = sprintf("\nEs folgt eine Liste mit jeweils Name und Link eines neu erstellten %s Eintrags:",
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
                        str_replace('{:TICKET:}', $item->getTicketId(), $urlTemplate)
                    );
                }
            }
            if (0 < count($deletedOldImportIds))
            {
                $reportLines[] = sprintf("\nHier eine Liste mit den jeweils nicht mehr gelieferten %s Einträgen:",
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
                        str_replace('{:TICKET:}', $item->getTicketId(), $urlTemplate)
                    );
                }
            }
        }
        $reportLines[] = "\nMidas wünscht noch einen guten Tag.\n";
        $reportMessage = implode(PHP_EOL, $reportLines);
        $mailTo = 'thorsten.schmitt-rink@berlinonline.de jens.bahr@berlinonline.de';
        mail($mailTo, $reportSubject, $reportMessage);
        echo sprintf("Mail to:\n%s\n----\nSubject:\n%s\n----\nMessage:\n%s\n----\n", 
            $mailTo, $reportSubject, $reportMessage);
    }
}
