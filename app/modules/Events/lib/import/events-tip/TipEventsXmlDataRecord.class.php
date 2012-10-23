<?php

/**
 * The EventsXmlDataRecord class is a concrete implementation of the EventsDataRecord base class.
 * It provides handling for mapping data coming from the xml import into the local event-record format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsXmlDataRecord extends EventsDataRecord
{
    /**
     * Maps the given data coming from the TipEventsXmlDataSource
     * to our local EventsDataRecord (EventsMasterRecord) format.
     *
     * @var $data A raw TipEventsXmlDataSource data row.
     *
     * @return array A restructured array ready for EventsDataRecord(/EventsMasterRecord) creation/update.
     */
    protected function parseData($data)
    {
        $categorySrc = $this->val($data, 'type');
        list($category, $subcategory) = $this->mapCategory($categorySrc);

        return array(
            self::PROP_IDENT => $this->mapId($data['identifier']),
            self::PROP_SOURCE => $this->config->getSetting(DataRecordConfig::CFG_SOURCE),
            self::PROP_ORIGIN => $this->config->getSetting(DataRecordConfig::CFG_ORIGIN),
            self::PROP_TIMESTAMP => new DateTime(),
            self::PROP_EVENT_SCHEDULE => $this->mapEventScheduleData($data),
            self::PROP_ARTICLES => $this->mapArticlesData($data),
            self::PROP_NAME => $this->val($data, 'name'),
            self::PROP_TEXT => $this->val($data, 'text'),
            self::PROP_CONTENT_CREATED => $this->val($data, 'erfDat'),
            self::PROP_CONTENT_UPDATED => $this->val($data, 'aktDat'),
            self::PROP_CATEGORY => $category,
            self::PROP_SUBCATEGORY => $subcategory,
            self::PROP_CATEGORY_SRC => $categorySrc,
            self::PROP_ORG_TITLE => $this->val($data, 'orgTitel'),
            self::PROP_SORT_TITLE => $this->val($data, 'sortTitle'),
            self::PROP_TICKETS => $this->val($data, 'tickets'),
            self::PROP_BOOK => $this->val($data, 'buch'),
            self::PROP_DURATION => $this->val($data, 'duration'),
            self::PROP_MEET_AT => $this->val($data, 'treffpunkt'),
            self::PROP_KIDS_INFO => $this->val($data, 'kinder'),
            self::PROP_WORKS => $this->val($data, 'werke'),
            self::PROP_ARCHIVE => $this->mapArchiveData($data),
            self::PROP_TAGS => $this->val($data, 'stile', array()),
            self::PROP_CLOSED => $this->val($data, 'gesperrt', FALSE),
            self::PROP_HAS_TIP_POINT => $this->val($data, 'tip-punkt'),
            self::PROP_PRICE => $this->val($data, 'eintrittspreis'),
            self::PROP_HIGHLIGHT => $this->val($data, 'hightlight'),
            self::PROP_INVOLVED_PEOPLE => $this->val($data, 'mitwirkende', array()),
            self::PROP_AGE_RESTRICTION => $this->val($data, 'alter'),
            self::PROP_ASSETS => $this->val($data, 'filepool', array())
        );
    }

    /**
     * Maps the given TipEventsXmlDataSource data segment,
     * that holds the schedule information to the EventsSchedule array structure.
     *
     * @var array $data
     *
     * @return array An unified array that is ready to fill a EventsSchedule.
     */
    protected function mapEventScheduleData(array $data)
    {
        $scheduleData = array('locations' => array());
        foreach ($this->val($data, 'ortstermine', array()) as $locationData)
        {
            $location = array(
                'locationId' => $this->mapId($locationData['veranstaltungsort']),
                'involvedPeople' => $this->val($locationData, 'mitwirkende', array()),
                'appointments' => array()
            );
            foreach ($locationData['termine'] as $appointmentData)
            {
                $startDate = $this->val($appointmentData, 'starttermin');
                $endDate = $this->val($appointmentData, 'endtermin');
                $location['appointments'][] = array(
                    'key' => $appointmentData['key'],
                    'contentUpdated' => $appointmentData['aktDat'],
                    'startDate' => ! empty($startDate) ? join(' ', $startDate) : NULL,
                    'endDate' => ! empty($endDate) ? join(' ', $endDate) : NULL,
                    'involvedPeople' => $this->val($appointmentData, 'mitwirkende', array()),
                    'isRecommended' => $this->val($appointmentData, 'tagestipp', FALSE),
                    'preText' => $this->val($appointmentData, 'vortext'),
                    'text' => $this->val($appointmentData, 'text'),
                    'postText' => $this->val($appointmentData, 'nachtext'),
                    'detail' => $this->val($appointmentData, 'einzelheit')
                );
            }
            $scheduleData['locations'][] = $location;
        }

        return $scheduleData;
    }

    /**
     * Maps the articles data segment of the given data,
     * to the article structure expected by the EventsArticle.
     *
     * @var array $data
     *
     * @return array An unified array that is ready to fill a list of EventsArticle.
     */
    protected function mapArticlesData(array $data)
    {
        $articles = array();
        foreach ($this->val($data, 'artikel', array()) as $articleData)
        {
            $article = $this->val($articleData, 'article', array());
            $pictureId = NULL;
            if (($imageUri = $this->val($article, 'bildURL', FALSE)))
            {
                // @notice we are ignoring the $articleData['typ'] value here as it only contains noise (jpg, png,gif,...)
                $metaData = array();
                $assetInfo = ProjectAssetService::getInstance()->put($imageUri, $metaData, FALSE);
                $pictureId = $assetInfo->getIdentifier();
            }

            $categorySrc = $this->val($article, 'typ');
            list($category, $subcategory) = $this->mapCategory($categorySrc);

            $articles[] = array(
                'identifier' => $this->mapId($this->val($article, 'key')),
                'title' => $this->val($article, 'name'),
                'text' => $this->val($article, 'text'),
                'category' => $category,
                'subcategory' => $subcategory,
                'categorySrc' => $categorySrc,
                'issue' => $this->val($article, 'heft'),
                'bx' => $this->val($article, 'bx'),
                'by' => $this->val($article, 'by'),
                'bu' => $this->val($article, 'bu'),
                'bf' => $this->val($article, 'bf'),
                'relatedPeople' => $this->val($article, 'person', array()),
                'pictureId' => $pictureId,
                'nodeValue' => $this->val($articleData, 'text'),
                'priority' => $this->val($articleData, 'priority'),
                'eventIds' => array_map(
                    array($this, 'mapId'), 
                    $this->val($article, 'veranstaltung', array())
                ),
                'locationIds' => array_map(
                    array($this, 'mapId'), 
                    $this->val($article, 'veranstaltungsort', array())
                ),
                'archiveIds' => array_map(
                    array($this, 'mapId'), 
                    $this->val($article, 'archiv', array())
                )
            );
        }

        return $articles;
    }

    /**
     * Maps the archive data segment of the given data,
     * to the archive structure expected by the EventsArchiveEntry.
     *
     * @var array $data
     *
     * @return array An unified array that is ready to fill a list of EventsArchiveEntry.
     */
    protected function mapArchiveData(array $data)
    {
        $archive = $this->val($data, 'archiv', FALSE);
        if (! $archive)
        {
            return NULL;
        }

        return array(
            'identifier' => $this->mapId($this->val($archive, 'foreignkey')),
            'contentCreated' => $this->val($archive, 'erfDat'),
            'contentUpdated' => $this->val($archive, 'aktDat'),
            'title' => $this->val($archive, 'originaltitel'),
            'sortTitle' => $this->val($archive, 'sortiertitel'),
            'ageRating' => $this->val($archive, 'fsk'),
            'duration' => $this->val($archive, 'dauer'),
            'year' => $this->val($archive, 'jahr'),
            'description' => $this->val($archive, 'text'),
            'tags' => $this->val($archive, 'stile', array()),
            'country' => $this->val($archive, 'land'),
            'involvedPeople' => $this->val($archive, 'mitwirkende', array()),
            'filepool' => $this->val($archive, 'filepool', array()),
            'hasTipPoint' => $this->val($archive, 'tip-punkt', FALSE),
            'isKidsMovie' => $this->val($archive, 'kinderfilm', FALSE),
            'rating' => $this->val($archive, 'bewertung', 0),
            'movieNumber' => $this->val($archive, 'filmNr'),
            'movieStart' => $this->val($archive, 'filmstart'),
            'originalArtwork' => $this->val($archive, 'vorlage'),
            'movieSeries' => $this->val($archive, 'filmreihe')
        );
    }

    protected function mapCategory($categorySrc)
    {
        $category = NULL;
        $subcategory = NULL;

        if (preg_match("#(musik_party)#i", $categorySrc))
        {
            $category = 'Musik & Party';
            $subcategory = 'Party';
        }
        elseif (preg_match("#(musik)#i", $categorySrc))
        {
            $category = 'Musik & Party';
            $subcategory = 'Konzerte';
        }
        elseif (preg_match("#(film)#i", $categorySrc))
        {
            $category = 'Kino & Film';
        }
        elseif (preg_match("#(ausstellung|kunst)#i", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Kunst & Museen';
        }
        elseif (preg_match("#(bühne)#i", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Theater & Bühne';
        }
        elseif (preg_match("#(literatur)#i", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Lesungen & Bücher';
        }
        elseif (preg_match("#(festival|führung|information|kinder|mode|sport|stadtleben|handel|heftinhalte|wasnoch)#i", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Stadtleben & Leute';
        }
        else
        {
            $category = 'Sonstiges';
        }

        return array($category, $subcategory);
    }

    /**
     * Convenience method that maps tip entity identifiers to their midas representation.
     *
     * @var string $identifier
     *
     * @return string
     */
    protected function mapId($identifier = NULL)
    {
        static $search = array('adr_', 'arc_', 'art_', 'v_');
        static $replace = array('place-', 'archive-', 'article-', 'event-');

        return (! $identifier) ? NULL : str_replace($search, $replace, $identifier);
    }

    /**
     * Small helper method, that gives you a piece of data
     * from an array if it exists and a predfined default otherwise.
     *
     * @var array $data 
     * @var $key 
     * @var $default
     *
     * @return mixed
     */
    protected function val(array $data, $key, $default = NULL)
    {
        return isset($data[$key]) ? $data[$key] : $default;
    }
}
