<?php

class NewsStatisticProvider
{
    const DISTRICT_ALL = 'all';

    const DISTRICT_CHAR = 'Charlottenburg';

    const DISTRICT_FRIED = 'Friedrichshain';

    const DISTRICT_HELLER = 'Hellersdorf';

    const DISTRICT_HOHEN = 'Hohenschönhausen';

    const DISTRICT_KÖP = 'Köpenick';

    const DISTRICT_KREUZ = 'Kreuzberg';

    const DISTRICT_LICHT = 'Lichtenberg';

    const DISTRICT_MAR = 'Marzahn';

    const DISTRICT_MIT = 'Mitte';

    const DISTRICT_NEUK = 'Neukölln';

    const DISTRICT_PANK = 'Pankow';

    const DISTRICT_PRENZ = 'Prenzlauer Berg';

    const DISTRICT_REI = 'Reinickendorf';

    const DISTRICT_SCHÖ = 'Schöneberg';

    const DISTRICT_SPAN = 'Spandau';

    const DISTRICT_STEG = 'Steglitz';

    const DISTRICT_TEMP = 'Tempelhof';

    const DISTRICT_TIER = 'Tiergarten';

    const DISTRICT_TREP = 'Treptow';

    const DISTRICT_WED = 'Wedding';

    const DISTRICT_WEISS = 'Weißensee';

    const DISTRICT_WIL = 'Wilmersdorf';

    const DISTRICT_ZEH = 'Zehlendorf';

    protected static $supportedDistricts = array(
        self::DISTRICT_CHAR, self::DISTRICT_FRIED, self::DISTRICT_HELLER, self::DISTRICT_HOHEN, self::DISTRICT_KÖP,
        self::DISTRICT_KREUZ, self::DISTRICT_LICHT, self::DISTRICT_MAR, self::DISTRICT_MIT, self::DISTRICT_NEUK,
        self::DISTRICT_PANK, self::DISTRICT_PRENZ, self::DISTRICT_REI, self::DISTRICT_SCHÖ, self::DISTRICT_SPAN,
        self::DISTRICT_STEG, self::DISTRICT_TEMP, self::DISTRICT_TIER, self::DISTRICT_TREP, self::DISTRICT_WED,
        self::DISTRICT_WEISS, self::DISTRICT_WIL, self::DISTRICT_ZEH
    );

    protected $elasticClient;

    protected $couchClient;

    public function __construct()
    {
        $this->elasticClient = new Elastica_Client(array(
            'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
            'port'      => AgaviConfig::get('elasticsearch.port', 9200),
            'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
        ));

        $this->couchClient = $this->getContext()->getDatabaseConnection('CouchWorkflow');
    }

    public function fetchDistrictStatistics($daysBack = 4, $district = self::DISTRICT_ALL)
    {
        $stats = array();

        if (self::DISTRICT_ALL === $district)
        {
            foreach ($this->getDistricts() as $curDistrict)
            {
                $stats[$curDistrict] = $this->fetchPublishedItemsCountForDistrict($curDistrict, $daysBack);
            }
        }
        else
        {
            if (! in_array($district, self::$supportedDistricts))
            {
                throw new InvalidArgumentException("The given district '$district' is not supported.");
            }
            $stats[$district] = $this->fetchPublishedItemsCountForDistrict($district, $daysBack);
        }
        return $stats;
    }

    public function getDistricts()
    {
        return self::$supportedDistricts;
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

        $stats = $this->mapItemsToPastDaysByDistrict(
            $this->elasticClient->getIndex('midas')->getType('item')->search($query),
            $district,
            $daysBack
        );
        $stats['totalCount'] = $this->fetchTotalPublishCountForDistrict($district, $daysBack);

        return $stats;
    }

    protected function mapItemsToPastDaysByDistrict(Elastica_ResultSet $results, $district, $daysBack)
    {
        $itemsLastWeek = 0;
        $itemsPerDay = array();
        for ($i = 0; $i < $daysBack; $i++)
        {
            $itemsPerDay[$i] = 0;
        }

        /* @var $workflowItemResult Elastica_Result */
        foreach($results as $workflowItemResult)
        {
            $workflowItem = new WorkflowItem($workflowItemResult->getData());
            /* @var $contentItem ContentItem */
            foreach ($workflowItem->getContentItems() as $contentItem)
            {
                if (($location = $contentItem->getLocation()) && $district == $location->getDistrict())
                {
                    // get the correct index, week or one of the past days and increment.
                    $daysAgoIndex = $this->determineDayIndex($contentItem, $daysBack);
                    if (0 <= $daysAgoIndex)
                    {
                        $itemsPerDay[$daysAgoIndex]++;
                    }
                    if ($this->wasPublishedDuringLastWeek($contentItem))
                    {
                        $itemsLastWeek++;
                    }
                }
            }
        }
        ksort($itemsPerDay);
        return array(
            'week' => $itemsLastWeek,
            'lastDays' => $itemsPerDay
        );
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
        $publishDateFilter = new Elastica_Filter_Range();
        return $publishDateFilter->addField(
            'contentItems.publishDate',
            array(
                'from' => $this->getDateByDaysPastFromNow($daysBack)->getNativeDateTime()->format(DATE_ISO8601),
                'to'   => $this->getTodaysDate()->getNativeDateTime()->format(DATE_ISO8601)
            )
        );
    }

    protected function determineDayIndex(ContentItem $item, $daysBack)
    {
        $publishedDate = $this->getItemsPublishDate($item);
        $curDaysBack = $daysBack;
        $daysAgo = $this->getDateByDaysPastFromNow($curDaysBack);
        if ($publishedDate->before($daysAgo))
        {
            return -1;
        }

        $fieldDiff = $daysAgo->fieldDifference($publishedDate, AgaviDateDefinitions::DATE);
        while (0 !== $fieldDiff && 0 <= $curDaysBack)
        {
            $curDaysBack--;
            $daysAgo = $this->getDateByDaysPastFromNow($curDaysBack);
            $fieldDiff = $daysAgo->fieldDifference($publishedDate, AgaviDateDefinitions::DATE);
        }

        return (0 === $fieldDiff) ? $curDaysBack : -1;
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
        $publishedDate = $this->getContext()->getTranslationManager()->createCalendar(
            new DateTime($item->getPublishDate())
        );
        return $publishedDate->after(
            $this->getDateByDaysPastFromNow(7)
        );
    }

    protected function getDateByDaysPastFromNow($daysBack)
    {
        $daysAgo = $this->getContext()->getTranslationManager()->createCalendar();
        $daysAgo->set(AgaviDateDefinitions::HOUR, 0);
        $daysAgo->set(AgaviDateDefinitions::MINUTE, 0);
        $daysAgo->set(AgaviDateDefinitions::SECOND, 0);
        if (0 < $daysBack)
        {
            $daysAgo->add(AgaviDateDefinitions::DATE, -$daysBack);
        }
        return $daysAgo;
    }

    protected function getTodaysDate()
    {
        $today = $this->getContext()->getTranslationManager()->createCalendar();
        $today->set1(AgaviDateDefinitions::HOUR_OF_DAY, 23);
        $today->set1(AgaviDateDefinitions::MINUTE, 59);
        $today->set1(AgaviDateDefinitions::SECOND, 59);
        return $today;
    }

    protected function getItemsPublishDate(IContentItem $item)
    {
        $publishedDate = $this->getContext()->getTranslationManager()->createCalendar(
            new DateTime($item->getPublishDate())
        );
        $publishedDate->set(AgaviDateDefinitions::HOUR, 23);
        $publishedDate->set(AgaviDateDefinitions::MINUTE, 59);
        $publishedDate->set(AgaviDateDefinitions::SECOND, 59);
        return $publishedDate;
    }

    protected function getContext()
    {
        return AgaviContext::getInstance();
    }
}

?>
