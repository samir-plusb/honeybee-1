<?php

class Shofi_MarkDuplicatesAction extends ShofiBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $categoryService = ShofiCategoriesWorkflowService::getInstance();
        $placeMatcher = new ShofiPlaceMatcher();
        $workflowService = ShofiWorkflowService::getInstance();
        $finder = ShofiFinder::create(ListConfig::fromArray(AgaviConfig::get('shofi.list_config')));

        $listStateParameters = array('limit' => 2500, 'offset' => 0);
        $categoryIdentifier = $parameters->getParameter('category', NULL);
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
                $entriesProcessed++;
                $categoryId = $item->getDetailItem()->getCategory();
                if (! $categoryId || $item->hasAttribute('potential_dups'))
                {
                    continue;
                }
                $categoryItem = $categoryService->fetchWorkflowItemById($categoryId);
                if (! $categoryItem)
                {
                    // this should/can not happen, perhaps write an error log just in case
                    continue;
                }

                $potentialDupsGroup = array();
                foreach ($placeMatcher->match($item) as $potentialDup)
                {
                    if ($categoryId === $potentialDup->getDetailItem()->getCategory())
                    {
                        $potentialDupsGroup[$potentialDup->getIdentifier()] = $potentialDup;
                        $entriesAffected++;
                    }
                }
                if (0 < count($potentialDupsGroup))
                {
                    $potentialDupsGroup[$item->getIdentifier()] = $item;
                    $entriesAffected++;
                    foreach ($potentialDupsGroup as $potentialMatch)
                    {
                        $potentialMatch->setAttribute('group_leader', $item->getIdentifier());
                        $potentialMatch->setAttribute('potential_dups', array_keys($potentialDupsGroup));
                        $workflowService->storeWorkflowItem($potentialMatch);
                    }
                }
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
