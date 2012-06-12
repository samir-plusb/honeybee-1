<?php

class Shofi_Categories_ExportAction extends ShofiCategoriesBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $finder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        $listState = ListState::fromArray(array(
            'limit' => 5000,
            'offset' => 0
        ));
        $entriesProcessed = 0;
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Exported " . $entriesProcessed . " categories ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
				$exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
            	if ($exportAllowed)
            	{
                    echo "Synching category: " . $item->getIdentifier() . " to the contentmachine..." . PHP_EOL;
                	$cmExport = new ContentMachineHttpExport(
                    	AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
                	);
                	if (! $cmExport->exportShofiCategory($item))
                	{
                    	echo "Error while sending data to fe for item : " . $item->getIdentifier() . PHP_EOL .
                             ", Error: " . print_r($cmExport->getLastErrors(), TRUE) . PHP_EOL;
                	}
            	}
            }
            $listState->setOffset(
                $listState->getOffset() + $listState->getLimit()
            );
            $entriesProcessed += $listState->getLimit();
        }
        return 'Success';
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