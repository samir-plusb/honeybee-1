<?php

class Shofi_FixDataAction extends ShofiBaseAction
{
    protected $categoryStore;

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $workflowService = ShofiWorkflowService::getInstance();
        $finder = ShofiFinder::create(ListConfig::fromArray(AgaviConfig::get('shofi.list_config')));
        $listState = ListState::fromArray(array('limit' => 2500, 'offset' => 0));
        
        $entriesProcessed = 0;
		$entriesAffected = 0;
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Exported " . $entriesProcessed . " places ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
                $detailItem = $item->getDetailItem();
                $category = $detailItem->getCategory();
                $furtherCategories = $detailItem->getAdditionalCategories();
                if (in_array($category, $furtherCategories))
                {
					$entriesAffected++;
                    echo (
                        "Double match for (primary)category-id: " . $category . 
                        " and place: " . $item->getIdentifier() . PHP_EOL
                    );
                    $detailItem->setAdditionalCategories(NULL);
                }
                $workflowService->storeWorkflowItem($item);
                //$this->exportPlace($item);
            }

            $listState->setOffset($listState->getOffset() + $listState->getLimit());
            $entriesProcessed += $listState->getLimit();
        }
		echo "Would have fixed " . $entriesAffected . " number of places.";
        return 'Success';
    }

    protected function exportPlace(ShofiWorkflowItem $workflowItem)
    {
        $exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
        if (TRUE === $exportAllowed)
        {
            if (! $cmExport->exportShofiPlace($item))
            {
                echo "Error while sending data to fe for (place)item : " . $item->getIdentifier() . PHP_EOL . 
                     ', Error: ' . print_r($cmExport->getLastErrors(), TRUE) . PHP_EOL;
            }
        }
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

    public function isSecure()
    {
        return FALSE;
    }
}
