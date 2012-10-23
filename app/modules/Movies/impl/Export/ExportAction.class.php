<?php

class Movies_ExportAction extends MoviesBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        $listState = ListState::fromArray(array(
            'limit' => 100,
            'offset' => 0
        ));
        $entriesProcessed = 0;
        $today = new DateTime();
        $export = new MoviesFrontendExport();
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Exported " . $entriesProcessed . " movies ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
                $screenings = $item->getMasterRecord()->getScreenings();
                $mayBeExported = empty($screenings);
                foreach ($screenings as $screening)
                {
                    $date = new DateTime($screening['date']);
                    $diff = $today->diff($date);

                    if (0 === $diff->invert)
                    {
                        $mayBeExported = TRUE;
                    }
                }
                if ($mayBeExported)
                {
                    $export->exportMovie($item);
                }
                else
                {
                    $export->deleteMovie($item);
                    // @todo remove the movie from the frontend if it exists.
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
