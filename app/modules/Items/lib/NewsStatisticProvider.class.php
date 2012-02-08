<?php

class NewsStatisticProvider
{
    protected $elasticClient;

    protected $couchClient;

    protected $today;

    protected static $districts = array(
        'Charlottenburg', 'Friedrichshain', 'Hellersdorf', 'Hohenschönhausen', 'Köpenick', 'Kreuzberg',
        'Lichtenberg', 'Marzahn', 'Mitte', 'Neukölln', 'Pankow', 'Prenzlauer Berg', 'Reinickendorf',
        'Schöneberg', 'Spandau', 'Steglitz', 'Tempelhof', 'Tiergarten', 'Treptow', 'Wedding',
        'Weißensee', 'Wilmersdorf', 'Zehlendorf'
    );

    public function __construct()
    {
        $this->elasticClient = new Elastica_Client(array(
            'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
            'port'      => AgaviConfig::get('elasticsearch.port', 9200),
            'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
        ));

        $this->couchClient = $this->getContext()->getDatabaseConnection('CouchWorkflow');
    }

    /**
     * Execute the read logic for this action.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     */
    public function fetchDistrictStatistics($daysBack = 4)
    {
        $stats = array();

        foreach (self::$districts as $district)
        {
            $stats[$district] = $this->fetchPublishedItemsCountForDistrict($district, $daysBack);
        }

        return $stats;
    }

    public function getTodaysDate()
    {
        $tm = $this->getContext()->getTranslationManager();
        $today = $tm->createCalendar();
        $today->set1(AgaviDateDefinitions::HOUR_OF_DAY, 23);
        $today->set1(AgaviDateDefinitions::MINUTE, 59);
        $today->set1(AgaviDateDefinitions::SECOND, 59);
        return $today;
    }

    public function getContext()
    {
        return AgaviContext::getInstance();
    }

    protected function fetchPublishedItemsCountForDistrict($district, $daysBack)
    {
        $stats = array(
            'totalCount' => $this->fetchTotalPublishCountForDistrict($district),
            'week' => 0,
            'lastDays' => array()
        );
        for ($i = 0; $i < $daysBack; $i++)
        {
            $stats['lastDays'][$i] = 0;
        }

        $query = new Elastica_Query(
            new Elastica_Query_MatchAll()
        );
        $query->setLimit(100000);
        $query->setFilter(
            $this->buildPublishedFilterForDistrict($district, $daysBack)
        );
        $itemsIndex = $this->elasticClient->getIndex('midas')->getType('item');
        $searchData = $itemsIndex->search($query);
        /* @var $workflowItemResult Elastica_Result */
        foreach($searchData->getResults() as $workflowItemResult)
        {
            $workflowItem = new WorkflowItem($workflowItemResult->getData());
            /* @var $contentItem ContentItem */
            foreach ($workflowItem->getContentItems() as $contentItem)
            {
                if (($location = $contentItem->getLocation()))
                {
                    if ($district == $location->getDistrict())
                    {
                        // get the correct index, week or one of the past days and increment.
                        $daysAgoIndex = $this->determineDayIndex($contentItem, $daysBack);
                        if (0 <= $daysAgoIndex)
                        {
                            $stats['lastDays'][$daysAgoIndex]++;
                        }
                        if ($this->wasPublishedDuringLastWeek($contentItem))
                        {
                            $stats['week']++;
                        }
                    }
                }
            }
        }
        return $stats;
    }

    protected function buildPublishedFilterForDistrict($district, $daysBack)
    {
        $filter = new Elastica_Filter_And();

        return $filter->addFilter(
            new Elastica_Filter_Term(
                array('currentState.workflow' => 'news')
            )
        )->addFilter(
            new Elastica_Filter_Term(
                array('contentItems.location.district' => mb_strtolower($district, 'utf8'))
            )
        )->addFilter(
            new Elastica_Filter_Exists('contentItems.publishDate')
        )->addFilter(
            $this->buildPublishedItemsDateRangeFilter(
                (7 <= $daysBack) ? $daysBack : 7
            )
        );
    }

    protected function buildPublishedItemsDateRangeFilter($daysBack)
    {
        $tm = $this->getContext()->getTranslationManager();
        $lowerDate = $tm->createCalendar();
        $lowerDate->add(AgaviDateDefinitions::DATE, -$daysBack);
        $lowerDate->set1(AgaviDateDefinitions::HOUR_OF_DAY, 0);
        $lowerDate->set1(AgaviDateDefinitions::MINUTE, 0);
        $lowerDate->set1(AgaviDateDefinitions::SECOND, 0);

        $publishDateFilter = new Elastica_Filter_Range();

        return $publishDateFilter->addField('contentItems.publishDate',
            array(
                'from' => $lowerDate->getNativeDateTime()->format(DATE_ISO8601),
                'to'   => $this->getTodaysDate()->getNativeDateTime()->format(DATE_ISO8601)
            )
        );
    }

    protected function determineDayIndex(ContentItem $item, $daysBack)
    {
        $tm = $this->getContext()->getTranslationManager();
        $publishedDate = $tm->createCalendar(
            new DateTime($item->getPublishDate())
        );

        $daysAgo = $tm->createCalendar();
        $daysAgo->set(AgaviDateDefinitions::HOUR, 0);
        $daysAgo->set(AgaviDateDefinitions::MINUTE, 0);
        $daysAgo->set(AgaviDateDefinitions::SECOND, 0);
        $daysAgo->add(AgaviDateDefinitions::DATE, -$daysBack);
        $curDaysBack = $daysBack;
        if ($publishedDate->before($daysAgo))
        {
            return -1;
        }
        $fieldDiff = $daysAgo->fieldDifference($publishedDate, AgaviDateDefinitions::DATE);

        while (1 < $fieldDiff && 0 < $curDaysBack)
        {
            $curDaysBack--;
            $daysAgo = $tm->createCalendar();
            $daysAgo->set(AgaviDateDefinitions::HOUR, 0);
            $daysAgo->set(AgaviDateDefinitions::MINUTE, 0);
            $daysAgo->set(AgaviDateDefinitions::SECOND, 0);
            $daysAgo->add(AgaviDateDefinitions::DATE, -$curDaysBack);
            $fieldDiff = $daysAgo->fieldDifference($publishedDate, AgaviDateDefinitions::DATE);
        }
        if (0 === $curDaysBack)
        {
            return -1;
        }
        return ($curDaysBack - 1);
    }

    protected function fetchTotalPublishCountForDistrict($district)
    {
        $result = $this->couchClient->getView(
            NULL,
            'designWorkflow',
            "contentItemsByDistrict",
            array('key' => $district)
        );
        if (! empty($result['rows']))
        {
            return $result['rows'][0]['value'];
        }
        return 0;
    }

    protected function wasPublishedDuringLastWeek(ContentItem $item)
    {
        $tm = $this->getContext()->getTranslationManager();
        $daysAgo = $tm->createCalendar();
        $daysAgo->set(AgaviDateDefinitions::HOUR, 0);
        $daysAgo->set(AgaviDateDefinitions::MINUTE, 0);
        $daysAgo->set(AgaviDateDefinitions::SECOND, 0);
        $daysAgo->add(AgaviDateDefinitions::DATE, -7);
        $publishedDate = $tm->createCalendar(
            new DateTime($item->getPublishDate())
        );
        return $publishedDate->after($daysAgo);
    }
}

?>
