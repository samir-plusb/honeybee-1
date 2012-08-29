<?php

class Events_ExportAction extends EventsBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $finder = EventsFinder::create(ListConfig::fromArray(
            AgaviConfig::get('events.list_config')
        ));
        $listState = ListState::fromArray(array(
            'limit' => 5000,
            'offset' => 0
        ));
        $entriesProcessed = 0;
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Exported " . $entriesProcessed . " Events ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
				// export Events here ...
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