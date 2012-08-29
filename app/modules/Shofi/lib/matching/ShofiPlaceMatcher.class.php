<?php

class ShofiPlaceMatcher
{
    // everything below 300 meters that matches fuzzy is considered a 'definite' match
    const MATCH_DIST_THRESHOLD = 50;

    protected $workflowService;

    public function __construct()
    {
        $this->workflowService = ShofiWorkflowService::getInstance();
        $this->csvDebugOutputHandle = fopen(
            AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR . 'matching_hotels.csv',
            'w'
        );
        fputcsv(
            $this->csvDebugOutputHandle, 
            array('Identifier', 'Name (what we have)', 'Incoming Name', 'Distance')
        );
    }

    public function match(ShofiWorkflowItem $shofiItem)
    {
        // make sure the incoming item has consistent geo coords
        $this->workflowService->localizeItem($shofiItem, TRUE);
        $placeName = $shofiItem->getCoreItem()->getName();
        $similarlyNamedPlaces = $this->findSimilarlyNamedPlaces($placeName);
        if (empty($similarlyNamedPlaces))
        {
            return NULL;
        }
        $matchedItem = NULL;
        foreach ($this->findPotentialMatches($shofiItem, $similarlyNamedPlaces) as $potentialMatch)
        {
            if (NULL === $matchedItem)
            {
                $matchedItem = $potentialMatch;
            }
            else if ($potentialMatch['distance'] < $matchedItem['distance'])
            {
                $matchedItem = $potentialMatch;
            }
        }

        return $matchedItem;
    }

    protected function findSimilarlyNamedPlaces($name)
    {
        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $fuzzyQuery = new Elastica_Query_Fuzzy();
        $fuzzyQuery->addField('coreItem.name.raw', array(
            "value" => $name,
            "boost" => 1.0,
            "min_similarity" => 0.65,
            "prefix_length" => 0
        ));

        return $finder->query($fuzzyQuery, NULL)->getItems();
    }

    protected function findPotentialMatches(ShofiWorkflowItem $shofiItem, array $matchCandidates)
    {
        $placeName = $shofiItem->getCoreItem()->getName();
        $potentialMatches = array();
        foreach ($matchCandidates as $matchCandidate)
        {
            $distance = $this->calculateDistance($shofiItem, $matchCandidate);
            if (NULL !== $distance && self::MATCH_DIST_THRESHOLD >= $distance)
            {
                $potentialMatches[] = array('item' => $matchCandidate, 'distance' => $distance);
            }
        }
        
        return $potentialMatches;
    }

    protected function calculateDistance(ShofiWorkflowItem $importItem, ShofiWorkflowItem $matchCandidate)
    {
        $locationPoint = $importItem->getCoreItem()->getLocation()->asGeoPoint();
        if (! $locationPoint)
        {
            return NULL;
        }
        // localize the match candiate in order to calc the distance afterwards.
        $this->workflowService->localizeItem($matchCandidate, TRUE);
        $matchPoint = $matchCandidate->getCoreItem()->getLocation()->asGeoPoint();
        if (! $matchPoint)
        {
            return NULL;
        }

        return $locationPoint->calculateDistance($matchPoint, GeoPoint::UNIT_METERS);
    }
}
