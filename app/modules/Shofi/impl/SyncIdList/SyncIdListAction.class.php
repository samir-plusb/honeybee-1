<?php

class Shofi_SyncIdListAction extends ShofiBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $cmExport = new ContentMachineHttpExport(
            AgaviConfig::get('shofi.cm_export_url')
        );
        $filepath = $parameters->getParameter('filename');
        if (! is_readable($filepath))
        {
            echo "Invalid Idlist list filepath.";
            return AgaviView::NONE;
        }

        $jsonData = file_get_contents($filepath);
        $idList = json_decode($jsonData, TRUE);
        if (! $idList || ! is_array($idList))
        {
            echo "Unable to parse idlist!";
            return AgaviView::NONE;
        }

        $chunksize = $parameters->getParameter('chunksize', 10);
        $shofiFinder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        foreach (array_chunk($idList, $chunksize) as $idsChunk)
        {
            $currentIds = array();
            foreach ($idsChunk as $identifier)
            {
                if (0 !== strpos($identifier, 'place-'))
                {
                    $identifier = 'place-' . $identifier;
                }
                $currentIds[] = $identifier;
            }
            $finderResult = $shofiFinder->findByIds($currentIds, 0, $chunksize);
            foreach ($finderResult->getItems() as $shofiItem)
            {
                $cmExport->exportShofiPlace($shofiItem);
            }
        }

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
