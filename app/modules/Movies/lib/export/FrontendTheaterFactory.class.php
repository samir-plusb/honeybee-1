<?php

class FrontendTheaterFactory
{
    public function createFromObject(IDataObject $theaterItem, array $options = array())
    {
        $excludeScreenings = array_key_exists('excludeScreenings', $options) ? $options['excludeScreenings'] : FALSE;
        // prepare backend data
        $data = $theaterItem->toArray();
        $exportDataKeys = array(
            'identifier' => 'identifier',
            'coreItem' => 'coreData',
            'salesItem' => 'salesData',
            'detailItem' => 'detailData',
            'attributes' => 'attributes',
            'lastModified' => 'lastModified'
        );
        $data['detailItem']['attachments'] = $this->prepareContentMachineAssetData(
            $theaterItem->getDetailItem()->getAttachments()
        );
        $data['salesItem']['attachments'] = $this->prepareContentMachineAssetData(
            $theaterItem->getSalesItem()->getAttachments()
        );

        // map data to the frontend structure
        $exportData = array();
        foreach ($exportDataKeys as $localKey => $exportKey)
        {
            $exportData[$exportKey] = $data[$localKey];
        }
        if (! $excludeScreenings)
        {
            $lists = $this->buildLists(
                $theaterItem,
                $this->getRelatedMovies($theaterItem)
            );
            $exportData['movies'] = $lists['movies'];
            $exportData['screenings'] = $lists['screenings'];
        }
        return FrontendTheaterDocument::fromArray($exportData);
    }

    public function getRelatedMovies(ShofiWorkflowItem $theaterItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        $result = $finder->find(ListState::fromArray(array(
            'offset' => 0,
            'limit' => 500,
            'filter' => array(
                'masterRecord.screenings.theaterId' => $theaterItem->getIdentifier()
            )
        )));
        return $result->getItems();
    }

    protected function buildLists(IDataObject $theaterItem, array $movies)
    {
        $movieFactory = new FrontendMovieFactory();

        $screeningList = array();
        $movieList = array();
        foreach ($movies as $movieItem)
        {
            $movieScreenings = $movieItem->getMasterRecord()->getScreenings();
            $frontendMovie = $movieFactory->createFromObject($movieItem, array('excludeScreenings' => TRUE));
            $movieId = $frontendMovie->getIdentifier();
            foreach ($movieScreenings as &$screening)
            {
                if ($screening['theaterId'] === $theaterItem->getIdentifier())
                {
                    unset($screening['theaterId']);
                    $screening['movieId'] = $movieId;
                    $screeningList[] = $screening;
                }
                if (! isset($movieList[$movieId]))
                {
                    $movieList[$movieId] = $frontendMovie->toArray();
                }
            }
        }

        return array(
            'movies' => array_values($movieList),
            'screenings' => $screeningList
        );
    }

    protected function prepareContentMachineAssetData(array $assetIds)
    {
        $assets = array();
        $assetService = ProjectAssetService::getInstance();
        foreach ($assetService->multiGet($assetIds) as $id => $asset)
        {
            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assets[] = array(
                'id' => $asset->getIdentifier(),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : ''
            );
        }
        return $assets;
    }
}

?>
