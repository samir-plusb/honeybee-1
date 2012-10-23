<?php

class Shofi_Categories_ExportAction extends ShofiCategoriesBaseAction
{
    protected $cmExport;

    public function initialize(AgaviExecutionContainer $container)
    {
        parent::initialize($container);

        $this->cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );
    }

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $workflowService = ShofiCategoriesWorkflowService::getInstance();
        $finder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        $listState = ListState::fromArray(array('limit' => 5000, 'offset' => 0));
        $entriesProcessed = 0;
		$affectedEntries = 0;
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Exported " . $entriesProcessed . " categories ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
                $wkgName = $item->getMasterRecord()->getName();
                $alias = $item->getMasterRecord()->getAlias();
				if (! empty($alias))
				{
                	echo ("Overwriting name: " . $wkgName . " with alias: " . $alias . PHP_EOL);
					$affectedEntries++;
					$item->getMasterRecord()->setName($alias);
                    $workflowService->storeWorkflowItem($item);
                    $this->exportCategory($item);
				}
            }

            $listState->setOffset($listState->getOffset() + $listState->getLimit());
            $entriesProcessed += $listState->getLimit();
        }
		echo "Totally " . $affectedEntries . " number of categories would have been modified." . PHP_EOL;
        return 'Success';
    }

    protected function exportCategory(ShofiCategoriesWorkflowItem $item)
    {
        $exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
        if (TRUE === $exportAllowed)
        {
            if (! $this->cmExport->exportShofiCategory($item))
            {
                echo "Error while sending data to fe for (category)item : " . $item->getIdentifier() . PHP_EOL . 
                     ', Error: ' . print_r($this->cmExport->getLastErrors(), TRUE) . PHP_EOL;
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

?>
