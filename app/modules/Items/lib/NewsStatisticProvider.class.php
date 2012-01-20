<?php

class NewsStatisticProvider
{
    protected $elasticClient;

    protected $today;

    public function __construct()
    {
        $tm = $this->getContext()->getTranslationManager();
        $this->today = $tm->createCalendar();
        $this->today->set1(AgaviDateDefinitions::HOUR_OF_DAY, 23);
        $this->today->set1(AgaviDateDefinitions::MINUTE, 59);
        $this->today->set1(AgaviDateDefinitions::SECOND, 59);

        $this->elasticClient = new Elastica_Client(array(
            'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
            'port'      => AgaviConfig::get('elasticsearch.port', 9200),
            'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
        ));
    }

    /**
     * Execute the read logic for this action.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function fetchStatistics($daysBack) // @codingStandardsIgnoreEnd
    {
        $itemsData = $this->findItemsWithTillNow($daysBack);
        $stats = $this->generateStats($itemsData['items']);

        return $stats;
    }

    protected function generateStats(array $items)
    {
        $stats = array(
            '_all' => array(
                'imported' => array(
                    'eversince' => 0,
                    'week' => 0,
                    'yesterday' => 0,
                    'today' => 0
                ),
                'published' => array(
                    'eversince' => 0,
                    'week' => 0,
                    'yesterday' => 0,
                    'today' => 0
                )
            )
        );
        $districts = array(
            'charlottenburg', 'friedrichshain', 'hellersdorf', 'hohenschönhausen', 'köpenick', 'kreuzberg',
            'lichtenberg', 'marzahn', 'mitte', 'neukölln', 'pankow', 'prenzlauer berg', 'reinickendorf',
            'schöneberg', 'spandau', 'steglitz', 'tempelhof', 'tiergarten', 'treptow', 'wedding',
            'weißensee', 'wilmersdorf', 'zehlendorf'
        );
        foreach ($districts as $district)
        {
            $stats[$district] = array(
                'imported' => array(
                    'eversince' => 0,
                    'week' => 0,
                    'yesterday' => 0,
                    'today' => 0
                ),
                'published' => array(
                    'eversince' => 0,
                    'week' => 0,
                    'yesterday' => 0,
                    'today' => 0
                )
            );
        }

        foreach ($items as $workflowItem)
        {
            $stats['_all']['imported']['week']++;
            $created = $workflowItem->getImportItem()->getCreated();
            $importItemTimeRubric = $this->resolveDateToTimeIndex($created['date']);
            if (in_array($importItemTimeRubric, array('today', 'yesterday')))
            {
                $stats['_all']['imported'][$importItemTimeRubric]++;
            }

            $contentItems = $workflowItem->getContentItems();
            if (empty($contentItems))
            {
                continue;
            }
            $importItemCounted = FALSE;
            foreach ($contentItems as $contentItem)
            {
                $publishDate = $contentItem->getPublishDate();
                $aDistrict = $contentItem->getLocation()->getAdministrativeDistrict();
                if (empty($publishDate) || empty($aDistrict))
                {
                    continue;
                }
                $aDistrict = strtolower($aDistrict);
                $contentItemTimeRubric = $this->resolveDateToTimeIndex($publishDate);
                if (! array_key_exists($aDistrict, $stats))
                {
                    continue;
                }
                if (! $importItemCounted)
                {
                    $stats[$aDistrict]['imported']['week']++;
                    $stats[$aDistrict]['imported'][$importItemTimeRubric]++;
                    $importItemCounted = TRUE;
                }
                $stats[$aDistrict]['published']['week']++;
                $stats[$aDistrict]['published'][$contentItemTimeRubric]++;
            }
        }
        return $stats;
    }

    protected function findItemsWithTillNow($daysBack)
    {
        $tm = $this->getContext()->getTranslationManager();
        $sevenDaysAgo = $tm->createCalendar();
        $sevenDaysAgo->add(AgaviDateDefinitions::DATE, -$daysBack);
        $sevenDaysAgo->set1(AgaviDateDefinitions::HOUR_OF_DAY, 0);
        $sevenDaysAgo->set1(AgaviDateDefinitions::MINUTE, 0);
        $sevenDaysAgo->set1(AgaviDateDefinitions::SECOND, 0);

        $publishDateFilter = new Elastica_Filter_Range();
        $publishDateFilter->addField('importItem.created.date',
            array(
                'from' => $sevenDaysAgo->getNativeDateTime()->format(DATE_ISO8601),
                'to'   => $this->today->getNativeDateTime()->format(DATE_ISO8601)
            )
        );
        $query = new Elastica_Query(
            new Elastica_Query_Term(
                array('currentState.workflow' => 'news')
            )
        );
        $query->setFilter($publishDateFilter);
        $query->setLimit(1000000);
        $index = $this->elasticClient->getIndex('midas');
        $type = $index->getType('item');
        $result = $type->search($query);

        $items = array();
        /* @var $items Elastica_Result */
        foreach($result->getResults() as $doc)
        {
            $items[] = new WorkflowItem($doc->getData());
        }
        return array(
            'items'      => $items,
            'totalCount' => $result->getTotalHits()
        );
    }

    // receive ISO8601 date string
    // returns week, yesterday or today
    protected function resolveDateToTimeIndex($dateString)
    {
        $tm = $this->getContext()->getTranslationManager();
        $yesterday = $tm->createCalendar();
        $yesterday->add(AgaviDateDefinitions::DATE, -1);
        $yesterday->set1(AgaviDateDefinitions::HOUR_OF_DAY, 23);
        $yesterday->set1(AgaviDateDefinitions::MINUTE, 59);
        $yesterday->set1(AgaviDateDefinitions::SECOND, 59);

        $date = $tm->createCalendar(new DateTime($dateString));
        $diff = $date->fieldDifference($yesterday, AgaviDateDefinitions::DAY_OF_YEAR);
        $category = 'week';

        if ($date->after($yesterday))
        {
            $category = 'today';
        }
        else if(0 == $diff)
        {
            $category = 'yesterday';
        }
        return $category;
    }

    protected function getContext()
    {
        return AgaviContext::getInstance();
    }
}

?>
