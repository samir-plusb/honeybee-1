<?php

/**
 * The NewsStatisticProvider is responseable for collecting statistics on items published per district.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Due to the broad usage of the Elastica lib.
 */
class NewsStatisticProvider
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Represents 'all' districts.
     * Is used to tell the provider to generate stats for all districts.
     */
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

    /**
     * Name of the elastic search index that is queried when gathering news-item information.
     */
    const ES_IDX_NAME = 'midas_news';

    /**
     * Name of the elastic search type that we query for news-items.
     */
    const ES_TYPE_NAME = 'news-item';

    /**
     * Name of the couchdb design document that contains a view we use to fetch total counts.
     */
    const COUCH_DESIGN_DOC = 'contentItems';

    /**
     * Name of the couchdb view (map & reduce) that provides access to the total number of content-items
     * that have been published for a given district.
     */
    const COUCH_VIEW_NAME = 'byDistrict';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An array containing the names of all districts that are supported for reporting.
     *
     * @type string[]
     */
    protected static $supportedDistricts = array(
        self::DISTRICT_CHAR, self::DISTRICT_FRIED, self::DISTRICT_HELLER, self::DISTRICT_HOHEN, self::DISTRICT_KÖP,
        self::DISTRICT_KREUZ, self::DISTRICT_LICHT, self::DISTRICT_MAR, self::DISTRICT_MIT, self::DISTRICT_NEUK,
        self::DISTRICT_PANK, self::DISTRICT_PRENZ, self::DISTRICT_REI, self::DISTRICT_SCHÖ, self::DISTRICT_SPAN,
        self::DISTRICT_STEG, self::DISTRICT_TEMP, self::DISTRICT_TIER, self::DISTRICT_TREP, self::DISTRICT_WED,
        self::DISTRICT_WEISS, self::DISTRICT_WIL, self::DISTRICT_ZEH
    );

    /**
     * Holds the elastic search index that holds our data.
     *
     * @type Elastica_Index
     */
    protected $midasIndex;

    /**
     * Holds the client instance used to talk to couchdb.
     *
     * @type ExtendedCouchDbClient
     */
    protected $couchClient;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    public static function create()
    {
        return new NewsStatisticProvider(
            AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
                NewsFinder::getElasticSearchDatabaseName()
            )->getResource(),
            AgaviContext::getInstance()->getDatabaseConnection(
                NewsWorkflowSupervisor::getCouchDbDatabasename()
            )
        );
    }

    /**
     * Create a new NewsStatisticProvider instance.
     *
     * @param Elastica_Index $elasticIndex The index that shall be used to query elastic search.
     * @param ExtendedCouchDbClient $couchClient The client to use when talking to couchdb.
     */
    public function __construct(Elastica_Index $elasticIndex, ExtendedCouchDbClient $couchClient)
    {
        $this->midasIndex = $elasticIndex;
        $this->couchClient = $couchClient;
    }

    /**
     * Provides an array containing information on how many content-items where published
     * per day for the given district. The data is provided on a per day base reaching back
     * $daysBack number of days.
     *
     * Example result structure for following parameters: $daysBack => 5, $district => DISTRICT_WED.
     * If multiple districts have been demanded, then the structure is repeated for each district.
     * <pre>
     * array(
     *     [Wedding] => Array
     *     (
     *         [week] => 27
     *         [totalCount] => 23
     *         [lastDays] => Array
     *         (
     *             [0] => 2
     *             [1] => 5
     *             [2] => 7
     *             [3] => 2
     *             [4] => 4
     *         )
     *     ),
     *     ...
     * )
     * </pre>
     *
     * @param int $daysBack The number of days to go back when collecting the stat data.
     * @param string $district One of the supported DISTRICT_* constants.
     *
     * @return array An assoc array holding a stats array for each demanded district.
     * @todo Passing an array of districts to process should be more flexible.
     */
    public function fetchDistrictStatistics($daysBack = 4, $district = self::DISTRICT_ALL)
    {
        $stats = array();
        $districts = self::getDistricts();
        if (self::DISTRICT_ALL === $district)
        {
            foreach ($districts as $curDistrict)
            {
                $stats[$curDistrict] = $this->fetchPublishedItemsCountForDistrict($daysBack, $curDistrict);
            }
        }
        else
        {
            if (! in_array($district, $districts))
            {
                throw new InvalidArgumentException("The given district '$district' is not supported.");
            }
            $stats[$district] = $this->fetchPublishedItemsCountForDistrict($daysBack, $district);
        }
        return $stats;
    }

    /**
     * Returns the districts that are supported by the stat provider.
     *
     * @return string[] An array of district names (, that refer to the class's DISTRICT_* constants)
     */
    public static function getDistricts()
    {
        return self::$supportedDistricts;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Collects various types of content-item counts,
     * such as items per day, per week and in total for the given district
     * reaching back the given number of $daysback.
     *
     * Example result structure for following parameters: $daysBack => 5, $district => DISTRICT_WED.
     * Atm you are provided a 'totalCount', a 'week' based and an 'items per day' count.
     * <pre>
     * array(
     *     [week] => 27
     *     [totalCount] => 23
     *     [lastDays] => Array
     *     (
     *         [0] => 2
     *         [1] => 5
     *         [2] => 7
     *         [3] => 2
     *         [4] => 4
     *     )
     * )
     * </pre>
     *
     * @param int $daysBack The number of days to go back when generating the content-item count.
     * @param string $district The district to fetch the content-item count for.
     *
     * @return array An assoc array holding the complete stats for the given district.
     */
    protected function fetchPublishedItemsCountForDistrict($daysBack, $district)
    {
        $query = new Elastica_Query(
            new Elastica_Query_MatchAll()
        );
        $query->setLimit(500000); // @todo Need to find sensefull control for this parameter.
        $query->setFilter(
            $this->buildPublishedFilterForDistrict(
                // we need items for at least a week to fill the 'week' count, so lets take the bigger value
                (7 <= $daysBack) ? $daysBack : 7,
                $district
            )
        );
        $itemsIndex = $this->midasIndex->getType(self::ES_TYPE_NAME);
        $stats = $this->mapItemsToPastDaysByDistrict($itemsIndex->search($query), $daysBack, $district);
        $stats['totalCount'] = $this->fetchTotalPublishCountForDistrict($district);
        return $stats;
    }

    /**
     * Returns an elastic search (and)filter that is setup to filter for published
     * items on a per ditrict base reaching the number of given $daysBack.
     * !CAUTION! Deleted items are not filtered out as they are supposed to be reflected by the
     * stats provided by this class (ask product management to obtain deeper knowledge on this requirement).
     *
     * @param int $daysBack The number of days to go back when fetching news-items.
     * @param string $district The district to use to filter the searched news-items.
     *
     * @return Elastica_Filter_And The prepared and ready to use filter.
     */
    protected function buildPublishedFilterForDistrict($daysBack, $district)
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
                $this->getDateByDaysPastFromNow($daysBack)
            )
        );
    }

    /**
     * Builds an elastic search daterange filter,
     * that reaches from the given $lowerDate until today.
     *
     * @param AgaviCalendar $lowerDate The date to use for the range filter's lower date.
     *
     * @return Elastica_Filter_Range The prepared date range filter.
     */
    protected function buildPublishedItemsDateRangeFilter(AgaviCalendar $lowerDate)
    {
        $publishDateFilter = new Elastica_Filter_Range();
        return $publishDateFilter->addField(
            'contentItems.publishDate',
            array(
                'from' => $lowerDate->getNativeDateTime()->format(DATE_ISO8601),
                'to'   => $this->getTodaysDate()->getNativeDateTime()->format(DATE_ISO8601)
            )
        );
    }

    /**
     * Takes the result of an elastic search query that was fired against the ES_TYPE_NAME type
     * of the ES_IDX_NAME index and returns an array reflecting the number of items,
     * that have been published to the given $district if they were published within
     * either the last week or within $daysBack from today.
     *
     * Example result structure for following parameters: $daysBack => 5, $district => 'Wedding'.
     * Each index beneath the result array's key 'lastDays' stands for one of the $daysBack,
     * whereas the index 0 represents 'today' and the last index maps to today - $daysBack.
     * <pre>
     * array(
     *     [week] => 27
     *     [lastDays] => Array
     *     (
     *         [0] => 2
     *         [1] => 5
     *         [2] => 7
     *         [3] => 2
     *         [4] => 4
     *     )
     * )
     * </pre>
     *
     * @param Elastica_ResultSet $results Holds the fresh result returned from querying elastic search.
     * @param int $daysBack The number of days to go back when fetching news-items.
     * @param string $district The district to filter the news-items for.
     *
     * @return array An assoc array holding data on the weekly and per-day based content-item count.
     */
    protected function mapItemsToPastDaysByDistrict(Elastica_ResultSet $results, $daysBack, $district)
    {
        $itemsLastWeek = 0;
        // Initialize our 'items per day' counts for the given number of $daysBack.
        $itemsPerDay = array();
        for ($i = 0; $i < $daysBack; $i++)
        {
            $itemsPerDay[$i] = 0;
        }
        $newsService = NewsWorkflowService::getInstance();
        /* @var $workflowItemResult Elastica_Result */
        foreach($results as $workflowItemResult)
        {
            $workflowItem = $newsService->createWorkflowItem($workflowItemResult->getData());
            /* @var $contentItem ContentItem */
            foreach ($workflowItem->getContentItems() as $contentItem)
            {
                if (($location = $contentItem->getLocation()) && $district == $location->getDistrict())
                {
                    // Get the correct index, week or one of the past days and increment the item count.
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
        return array(
            'lastDays' => $itemsPerDay,
            'week' => $itemsLastWeek
        );
    }

    /**
     * Return a 'past days index' for the given content-item,
     * hence find out if the item was published within $dyasBack from today
     * and if so, then on which specific day within the latter range.
     * The returned value represents the items publish day in form of an integer,
     * starting with 0 (today) and +1 for every day back.
     * So yesterday would be 1 and the day before yesterday 2 etc.
     *
     * @param IContentItem $item The content-item that which's corresponding days-back index shall be determined.
     * @param int $daysBack The maximum number of days to go back when searching for an items index.
     *
     * @return int Either a value between 0 and ($daysBack - 1) or -1 if the item is out of range.
     */
    protected function determineDayIndex(IContentItem $item, $daysBack)
    {
        $curDaysBack = $daysBack;
        $daysAgo = $this->getDateByDaysPastFromNow($curDaysBack);
        $publishedDate = $this->getItemsPublishDate($item);
        if ($publishedDate->before($daysAgo))
        {
            return -1;
        }
        while (! $publishedDate->equals($daysAgo) && 0 < $curDaysBack)
        {
            $daysAgo = $this->getDateByDaysPastFromNow(--$curDaysBack);
        }
        return $publishedDate->equals($daysAgo) ? $curDaysBack : -1;
    }

    /**
     * Tells if a given content-item was published within the last week from now.
     *
     * @param IContentItem $item The content-item to inspect.
     *
     * @return bool
     */
    protected function wasPublishedDuringLastWeek(IContentItem $item)
    {
        $publishedDate = $this->getTranslationManager()->createCalendar(
            strtotime($item->getPublishDate())
        );
        return $publishedDate->after(
            $this->getDateByDaysPastFromNow(7)
        );
    }

    /**
     * Returns the total number of items,
     * that have been published for the gien district so far.
     *
     * @param string $district The name (DISTRICT_* constant) of the district which's items shall be counted.
     *
     * @return int
     */
    protected function fetchTotalPublishCountForDistrict($district)
    {
        $result = $this->couchClient->getView(
            NULL,
            self::COUCH_DESIGN_DOC,
            self::COUCH_VIEW_NAME,
            array('key' => $district)
        );
        if (! empty($result['rows']))
        {
            return $result['rows'][0]['value'];
        }
        return 0;
    }


    // ---------------------------------- </WORKING METHODS> -------------------------------------


    // ---------------------------------- <HELPER METHODS> ---------------------------------------

    /**
     * Return an AgaviCalendar instance that reflects the date $daysBack from now.
     *
     * @param int $daysBack The number of days to go back.
     *
     * @return AgaviCalendar A fresh agavi calendar instance with a time set to 00:00:00.000.
     */
    protected function getDateByDaysPastFromNow($daysBack)
    {
        $daysAgo = $this->getTranslationManager()->createCalendar();
        $daysAgo->set(AgaviDateDefinitions::HOUR_OF_DAY, 0);
        $daysAgo->set(AgaviDateDefinitions::MINUTE, 0);
        $daysAgo->set(AgaviDateDefinitions::SECOND, 0);
        $daysAgo->set(AgaviDateDefinitions::MILLISECOND, 0);
        if (0 < $daysBack)
        {
            $daysAgo->add(AgaviDateDefinitions::DATE, -$daysBack);
        }
        return $daysAgo;
    }

    /**
     * Return an AgaviCalendar instance that reflects the given content-item's publish-date.
     *
     * @param IContentItem $item The content-item to fetch the publish date from.
     *
     * @return AgaviCalendar A fresh agavi calendar instance with a time set to 00:00:00.000.
     */
    protected function getItemsPublishDate(IContentItem $item)
    {
        $publishedDate = $this->getTranslationManager()->createCalendar(
            strtotime($item->getPublishDate())
        );
        $publishedDate->set(AgaviDateDefinitions::HOUR_OF_DAY, 0);
        $publishedDate->set(AgaviDateDefinitions::MINUTE, 0);
        $publishedDate->set(AgaviDateDefinitions::SECOND, 0);
        $publishedDate->set(AgaviDateDefinitions::MILLISECOND, 0);
        return $publishedDate;
    }

    /**
     * Helper method used by the buildPublishedItemsDateRangeFilter method
     * in order to build it's filter in a way that will consider items,
     * that have been published until today's last possible millisecond.
     *
     * @return AgaviCalendar A fresh agavi calendar instance.
     */
    protected function getTodaysDate()
    {
        $today = $this->getTranslationManager()->createCalendar();
        $today->set1(AgaviDateDefinitions::HOUR_OF_DAY, 23);
        $today->set1(AgaviDateDefinitions::MINUTE, 59);
        $today->set1(AgaviDateDefinitions::SECOND, 59);
        $today->set(AgaviDateDefinitions::MILLISECOND, 999);
        return $today;
    }

    /**
     * Helper method for obtaining agavi's translation manager.
     *
     * @return AgaviTranslationManager
     */
    protected function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    // ---------------------------------- </HELPER METHODS> --------------------------------------
}

?>
