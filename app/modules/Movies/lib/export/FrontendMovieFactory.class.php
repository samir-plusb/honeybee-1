<?php

class FrontendMovieFactory
{
    public function createFromObject(IDataObject $dataObject, array $options = array())
    {
        $data = $dataObject->getMasterRecord()->toArray();
        $data['identifier'] = $dataObject->getIdentifier();
        $screenings = $dataObject->getMasterRecord()->getScreenings();
        $excludeScreenings = array_key_exists('excludeScreenings', $options) ? $options['excludeScreenings'] : FALSE;
        if ($excludeScreenings)
        {
            unset($data['screenings']);
        }
        else
        {
            $data['theaters'] = $this->buildTheatersList(
                $this->getRelatedTheaters($dataObject)
            );
        }
        return FrontendMovieDocument::fromArray($data);
    }

    protected function buildTheatersList(array $theaterItems)
    {
        $theaterList = array();
        $theaterFactory = new FrontendTheaterFactory();
        $factoryOptions = array('excludeScreenings' => TRUE);
        foreach ($theaterItems as $theaterItem)
        {
            $frontendDocument = $theaterFactory->createFromObject($theaterItem, $factoryOptions);
            $theaterList[] = $frontendDocument->toArray();
        }
        return $theaterList;
    }

    public function getRelatedTheaters(MoviesWorkflowItem $movieItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        return $finder->findRelatedTheaters($movieItem);
    }
}

?>
