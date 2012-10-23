<?php

class FrontendMovieFactory extends BaseExportDocumentFactory
{
    public function createFromObject(IDataObject $dataObject, array $options = array())
    {
        $documentData = $dataObject->getMasterRecord()->toArray();
        $documentData['identifier'] = $this->buildDocumentIdentifier($dataObject);
        $documentData['slug'] = $this->buildSlug($dataObject);
        $documentData['screenings'] = $this->mapScreenings($dataObject);
        $documentData['media'] = $this->mapMedia($documentData['media']);
        $documentData['topMovie'] = $documentData['isRecommendation'];

        return FrontendMovieDocument::fromArray($documentData);
    }

    public function buildDocumentIdentifier(IDataObject $dataObject)
    {
        return 'movie-' . $dataObject->getExportId();
    }

    public function getSlugPattern()
    {
        return '<masterRecord.title>-<identifier>';
    }

    protected function mapScreenings(MoviesWorkflowItem $movieItem)
    {
        $theaterExportIdMap = $this->buildTheatersExportIdMap($movieItem);
        $preparedScreenings = array();
        foreach ($movieItem->getMasterRecord()->getScreenings() as $screening)
        {
            $importId = $screening['theaterId'];
            if (isset($theaterExportIdMap[$importId]))
            {
                $screening['theaterId'] = $theaterExportIdMap[$importId];
                $preparedScreenings[] = $screening;
            }
        }
        return $preparedScreenings;
    }

    protected function buildTheatersExportIdMap(MoviesWorkflowItem $movieItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        $theaterItems = $finder->findRelatedTheaters($movieItem);

        $theaterList = array();
        $theaterFactory = new FrontendTheaterFactory();
        foreach ($theaterItems as $theaterItem)
        {
            $frontendId = $theaterFactory->buildDocumentIdentifier($theaterItem);
            $importId = NULL;
            foreach ($theaterItem->getAttribute('import_ids', array()) as $importId)
            {
                $parts = explode(':', $importId);
                if (2 == count($parts) && 'theaters-telavision' === $parts[0])
                {
                    $importId = $parts[1];
                }
            }
            if ($importId)
            {
                $theaterList[$importId] = $frontendId;
            }
            else
            { // shouldn't happen
                error_log("Uncountered a referenced theater without import origin data.");
            }
        }
        return $theaterList;
    }

    protected function slugifyIdentifier(IDataObject $dataObject)
    {
        return str_replace('movie-', '', $this->buildDocumentIdentifier($dataObject));
    }

    protected function mapMedia(array $media)
    {
        $mediaData = array(
            'trailers' => $media['trailers']
        );
        $assetKeys = array('scene', 'poster', 'poster600', 'hdPic');
        $galleryKeys = array('standard', 'big');

        foreach ($assetKeys as $key)
        {
            if (isset($media['images'][$key]))
            {
                $mediaData['images'][$key] = $this->buildAssetId($media['images'][$key]);
            }
        }
        foreach ($galleryKeys as $key)
        {
            if (isset($media['galleries'][$key]))
            {
                $mediaData['galleries'][$key] = $this->buildAssetIds($media['galleries'][$key]);
            }
        }
        return $mediaData;
    }
}
