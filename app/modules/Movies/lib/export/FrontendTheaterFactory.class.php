<?php

class FrontendTheaterFactory extends BaseExportDocumentFactory
{
    public function createFromObject(IDataObject $dataObject, array $options = array())
    {
        $documentData = $this->mapData($dataObject);
        $documentData['identifier'] = $this->buildDocumentIdentifier($dataObject);
        $documentData['slug'] = $this->buildSlug($dataObject);
        $documentData['screenings'] = $this->buildScreeningList($dataObject);
        $documentData['additionalInfo'] = $dataObject->getAttribute('prices', '');

        return FrontendTheaterDocument::fromArray($documentData);
    }

    public function buildDocumentIdentifier(IDataObject $dataObject)
    {
        return 'theater-' . $dataObject->getExportId();
    }

    public function getSlugPattern()
    {
        return '<coreItem.name>-<identifier>';
    }

    protected function mapData(IDataObject $theaterItem)
    {
        // prepare backend data
        $data = $theaterItem->toArray();
        $exportDataKeys = array(
            'coreItem' => 'coreData',
            'salesItem' => 'salesData',
            'detailItem' => 'detailData',
            'attributes' => 'attributes',
            'lastModified' => 'lastModified'
        );
        $data['detailItem']['attachments'] = $this->buildAssetIds(
            $theaterItem->getDetailItem()->getAttachments()
        );
        $data['salesItem']['attachments'] = $this->buildAssetIds(
            $theaterItem->getSalesItem()->getAttachments()
        );
        // map data to the frontend structure
        $exportData = array();
        foreach ($exportDataKeys as $localKey => $exportKey)
        {
            $exportData[$exportKey] = $data[$localKey];
        }
        return $exportData;
    }

    protected function buildScreeningList(IDataObject $theaterItem)
    {
        $theaterImportId = NULL;
        foreach ($theaterItem->getAttribute('import_ids', array()) as $importId)
        {
            $parts = explode(':', $importId);
            if (2 == count($parts) && 'theaters-telavision' === $parts[0])
            {
                $theaterImportId = $parts[1];
            }
        }

        $movieFactory = new FrontendMovieFactory();
        $screeningList = array();
        foreach ($this->getRelatedMovies($theaterItem) as $movieImportId => $movieItem)
        {
            $movieScreenings = $movieItem->getMasterRecord()->getScreenings();
            $movieId = $movieFactory->buildDocumentIdentifier($movieItem);
            foreach ($movieScreenings as &$screening)
            {
                if ($screening['theaterId'] === $theaterImportId)
                {
                    unset($screening['theaterId']);
                    $screening['movieId'] = $movieId;
                    $screeningList[] = $screening;
                }
            }
        }
        return $screeningList;
    }

    protected function getRelatedMovies(ShofiWorkflowItem $theaterItem)
    {
        $moviesList = array();
        $finder = MoviesFinder::create(ListConfig::fromArray(AgaviConfig::get('movies.list_config')));
        foreach ($finder->findRelatedMovies($theaterItem) as $movieItem)
        {
            $importId = NULL;
            foreach ($movieItem->getAttribute('import_ids', array()) as $importId)
            {
                $parts = explode(':', $importId);
                if (2 == count($parts) && 'movies-telavision' === $parts[0])
                {
                    $importId = $parts[1];
                }
            }
            if ($importId)
            {
                $moviesList[$importId] = $movieItem;
            }
            else
            { // shouldn't happen
                error_log("Uncountered a referenced movie without import origin data.");
            }
        }
        return $moviesList;
    }

    protected function slugifyIdentifier(IDataObject $dataObject)
    {
        return str_replace('theater-', '', $this->buildDocumentIdentifier($dataObject));
    }
}
