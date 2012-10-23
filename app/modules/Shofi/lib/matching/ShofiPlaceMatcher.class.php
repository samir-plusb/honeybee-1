<?php

class ShofiPlaceMatcher
{
    // everything below this distance that matches our fuzzy-like-this is considered a 'potential' match
    const MATCH_DIST_THRESHOLD = 70;

    protected $workflowService;

    public function __construct()
    {
        $this->workflowService = ShofiWorkflowService::getInstance();
    }

    public function matchClosest(ShofiWorkflowItem $shofiItem)
    {
        $closestMatch = NULL;
        $matchMode = 'approx';
        $matchesByDistance = array();
        $smallestDistance = FALSE;
        foreach ($this->match($shofiItem) as $potentialMatch)
        {
            $distance = (int)$potentialMatch['distance'];
            if (! $smallestDistance || $smallestDistance > $distance)
            {
                $smallestDistance = $distance;
            }
            if (! isset($matchesByDistance[$distance]))
            {
                $matchesByDistance[$distance] = array();
            }
            $matchesByDistance[$smallestDistance][] = $potentialMatch['item'];
        }

        if (isset($matchesByDistance[$smallestDistance]))
        {
            $currentDistance = -1;
            foreach ($matchesByDistance[$smallestDistance] as $matchedItem)
            {
                $matchedName = strtolower($matchedItem->getCoreItem()->getName());
                $incomingName = strtolower($shofiItem->getCoreItem()->getName());
                $nameDistance = levenshtein($matchedName, $incomingName);
                if ($nameDistance == 0) 
                {
                    $closestMatch = $matchedItem;
                    $matchMode = 'exact';
                    break;
                }
                if ($nameDistance <= $currentDistance || $currentDistance < 0) 
                {
                    $closestMatch  = $matchedItem;
                    $currentDistance = $nameDistance;
                }
            }
        }
        return ($closestMatch) ? array(
            'item' => $closestMatch, 
            'distance' => $smallestDistance, 
            'exactly_same' => ('exact' === $matchMode)
        ) : NULL;
    }

    public function match(ShofiWorkflowItem $shofiItem)
    {
        // make sure the incoming item has consistent geo coords
        $this->workflowService->localizeItem($shofiItem, TRUE);
        $coreItem = $shofiItem->getCoreItem();
        $placeName = $coreItem->getName();

        $matches = array();
        if (($coords = $coreItem->getLocation()->getCoordinates()))
        {
            $matches = $this->filterPotentialMatches(
                $shofiItem,
                $this->findSimilarPlaces($shofiItem, $coords)
            );
        }
        return $matches;
    }

    protected function findSimilarPlaces(ShofiWorkflowItem $shofiItem, array $coords)
    {
        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $namesLikeThisQuery = new Elastica_Query_FuzzyLikeThis();
        $namesLikeThisQuery->addFields(array('coreItem.name.raw'))
            ->setLikeText($shofiItem->getCoreItem()->getName())
            ->setMinSimilarity(0.3);

        $geoFilter = new Elastica_Filter_Or();
        $geoFilter->addFilter(
            new Elastica_Filter_Missing('coreItem.location.coordinates')
        )->addFilter(new Elastica_Filter_GeoDistance(
            'coreItem.location.coordinates', $coords, self::MATCH_DIST_THRESHOLD)
        );

        $geoAndCategoryFilter = new Elastica_Filter_And();
        $geoAndCategoryFilter->addFilter($geoFilter)->addFilter(
            new Elastica_Filter_Term(
                array('detailItem.category' => $shofiItem->getDetailItem()->getCategory())
            )
        );

        return $finder->query($namesLikeThisQuery, $geoAndCategoryFilter)->getItems();
    }

    protected function filterPotentialMatches(ShofiWorkflowItem $shofiItem, array $matchCandidates)
    {
        $placeName = $shofiItem->getCoreItem()->getName();
        $potentialMatches = array();
        foreach ($matchCandidates as $matchCandidate)
        {
            $distance = $this->calculateDistance($shofiItem, $matchCandidate);
            if (FALSE !== $distance && self::MATCH_DIST_THRESHOLD >= $distance)
            {
                $potentialMatches[] = array('item' => $matchCandidate, 'distance' => $distance);
            }
        }
        
        return $potentialMatches;
    }

    protected function calculateDistance(ShofiWorkflowItem $importItem, ShofiWorkflowItem $matchCandidate)
    {
        $locationPoint = $importItem->getCoreItem()->getLocation()->asGeoPoint();
        $matchPoint = $matchCandidate->getCoreItem()->getLocation()->asGeoPoint();
        return (! $locationPoint || ! $matchPoint) ? 
            FALSE : $locationPoint->calculateDistance($matchPoint, GeoPoint::UNIT_METERS);
    }
}
