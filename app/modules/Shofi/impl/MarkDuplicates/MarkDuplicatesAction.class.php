<?php

class Shofi_MarkDuplicatesAction extends ShofiBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $categoryIdentifier = $parameters->getParameter('category', NULL);
        $categoryService = ShofiCategoriesWorkflowService::getInstance();

        $workflowService = ShofiWorkflowService::getInstance();
        $finder = ShofiFinder::create(ListConfig::fromArray(AgaviConfig::get('shofi.list_config')));
        $listStateParameters = array('limit' => 2500, 'offset' => 0);
        if ($categoryIdentifier)
        {
            $listStateParameters['filter'] = array('detailItem.category' => $categoryIdentifier);
        }
        $listState = ListState::fromArray($listStateParameters);
        
        $entriesProcessed = 0;
        $entriesAffected = 0;
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Matched " . $entriesProcessed . " places for duplicates till now ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
                $categoryId = $item->getDetailItem()->getCategory();
                if (! $categoryId)
                {
                    continue;
                }
                $categoryItem = $categoryService->fetchWorkflowItemById($categoryId);
                if (! $categoryItem)
                {
                    // this should not happen, perhaps write an error log
                    continue;
                }
                $dupMarker = 'potential_dups_' . strtolower($categoryItem->getMasterRecord()->getName());
                $markedEntries = 0;
                $potentialDups = $finder->findPotentialDuplicates($item)->getItems();
                foreach ($potentialDups as $potentialDup)
                {
                    if ($categoryId === $potentialDup->getDetailItem()->getCategory())
                    {
                        $markedEntries++;
                        $potentialDup->setAttribute('conflict_state', $dupMarker);
                        $workflowService->storeWorkflowItem($potentialDup);
                    }
                }
                if (0 < $markedEntries)
                {
                    $item->setAttribute('conflict_state', $dupMarker);
                    $workflowService->storeWorkflowItem($item);
                    $entriesAffected++;
                }
                else if ($item->getAttribute('conflict_state', FALSE))
                {
                    $item->removeAttribute('conflict_state');
                    $workflowService->storeWorkflowItem($item);
                }
                $entriesProcessed++;
            }

            $listState->setOffset($listState->getOffset() + $listState->getLimit());
        }
        echo "Checked " . $entriesProcessed . " places for duplicates." . PHP_EOL;
        echo "Found duplicates for " . $entriesAffected . " places." . PHP_EOL;
        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }

    protected function printMemUsage()
    {
        $mem_usage = memory_get_usage(true);
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            echo round($mem_usage/1024,2)." kilobytes";
        else
            echo round($mem_usage/1048576,2)." megabytes";
        echo PHP_EOL;
    }
}
