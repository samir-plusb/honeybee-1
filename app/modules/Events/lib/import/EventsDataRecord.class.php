<?php

/**
 * The EventsDataRecord exposes a unified array structure provided by all event datasources,
 * which maps to what the EventsWorkflowItem/EventsMasterRecord expect for creation/updating.
 * This class serves as a base for all datarecord implementations inside the Events module.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import
 */
abstract class EventsDataRecord extends BaseDataRecord
{
    /**
     * Holds the name of our 'name' property.
     */
    const PROP_NAME = 'name';

    /**
     * Holds the name of our 'text' property.
     */
    const PROP_TEXT = 'text';

    /**
     * Holds the name of our 'contentCreated' property.
     */
    const PROP_CONTENT_CREATED = 'contentCreated';

    /**
     * Holds the name of our 'contentUpdated' property.
     */
    const PROP_CONTENT_UPDATED = 'contentUpdated';

    /**
     * Holds the name of our 'eventSchedule' property.
     */
    const PROP_EVENT_SCHEDULE = 'eventSchedule';

    /**
     * Holds the name of our 'category' property.
     */
    const PROP_CATEGORY = 'category';

    /**
     * Holds the name of our 'subcategory' property.
     */
    const PROP_SUBCATEGORY = 'subcategory';

    /**
     * Holds the name of our 'categorySrc' property.
     */
    const PROP_CATEGORY_SRC = 'categorySrc';

    /**
     * Holds the name of our 'assets' property.
     */
    const PROP_ASSETS = 'assets';

    /**
     * Holds the name of our 'archive' property.
     */
    const PROP_ARCHIVE = 'archive';

    /**
     * Holds the name of our 'orgTitle' property.
     */
    const PROP_ORG_TITLE = 'orgTitle';

    /**
     * Holds the name of our 'sortTitle' property.
     */
    const PROP_SORT_TITLE = 'sortTitle';

    /**
     * Holds the name of our 'tickets' property.
     */
    const PROP_TICKETS = 'tickets';

    /**
     * Holds the name of our 'book' property.
     */
    const PROP_BOOK = 'book';

    /**
     * Holds the name of our 'duration' property.
     */
    const PROP_DURATION = 'duration';

    /**
     * Holds the name of our 'meetAt' property.
     */
    const PROP_MEET_AT = 'meetAt';

    /**
     * Holds the name of our 'highlight' property.
     */
    const PROP_HIGHLIGHT = 'highlight';

    /**
     * Holds the name of our 'kidsInfo' property.
     */
    const PROP_KIDS_INFO = 'kidsInfo';

    /**
     * Holds the name of our 'works' property.
     */
    const PROP_WORKS = 'works';

    /**
     * Holds the name of our 'involvedPeople' property.
     */
    const PROP_INVOLVED_PEOPLE = 'involvedPeople';

    /**
     * Holds the name of our 'ageRestriction' property.
     */
    const PROP_AGE_RESTRICTION = 'ageRestriction';

    /**
     * Holds the name of our 'price' property.
     */
    const PROP_PRICE = 'price';

    /**
     * Holds the name of our 'tags' property.
     */
    const PROP_TAGS = 'tags';

    /**
     * Holds the name of our 'closed' property.
     */
    const PROP_CLOSED = 'closed';

    /**
     * Holds the name of our 'articles' property.
     */
    const PROP_ARTICLES = 'articles';

    /**
     * Holds the name of our 'hasTipPoint' property.
     */
    const PROP_HAS_TIP_POINT = 'hasTipPoint';

    /**
     * Holds the event's name.
     *
     * @var string $name
     */
    protected $name;

    /**
     * Holds a text describing the event.
     *
     * @var string $text
     */
    protected $text;

    /**
     * Holds the date at which the event's content was created.
     *
     * @var string $contentCreateDate
     */
    protected $contentCreateDate;

    /**
     * Holds the date at which the event's content was last updated.
     *
     * @var string $contentUpdateDate
     */
    protected $contentUpdateDate;

    /**
     * Holds the event's schedule.
     *
     * @var EventsSchedule $eventSchedule
     */
    protected $eventSchedule;

    /**
     * Holds the event's category.
     *
     * @var string $category
     */
    protected $category;

    /**
     * Holds the event's subcategory.
     *
     * @var string $subcategory
     */
    protected $subcategory;

    /**
     * Holds the event's category source (before mapped/matched).
     *
     * @var string $categorySrc
     */
    protected $categorySrc;

    /**
     * Holds the event's assets in form of an id list.
     *
     * @var array $assets
     */
    protected $assets;  

    /**
     * Holds the event's archive entry.
     * Is mostly shipped along with movies.
     *
     * @var array $archive
     */
    protected $archive;

    /**
     * Holds the event's original title.
     *
     * @var string $orgTitle
     */
    protected $orgTitle;

    /**
     * Holds the event title in a different version used for sorting.
     *
     * @var string $sortTitle
     */
    protected $sortTitle;

    /**
     * Holds the event's tickets.
     *
     * @var string $tickets
     */
    protected $tickets;

    /**
     * Holds the book an event might base on.
     *
     * @var string $book
     */
    protected $book;

    /**
     * Holds the event's duration.
     *
     * @var string $duration
     */
    protected $duration;

    /**
     * Holds the event's meetup location.
     *
     * @var string $meetAt
     */
    protected $meetAt;

    /**
     * Holds the event's highlight.
     *
     * @var string $highlight
     */
    protected $highlight;

    /**
     * Holds information concerning kids.
     *
     * @var string $kidsInfo
     */
    protected $kidsInfo;

    /**
     * Holds any works an event might base on a relate with.
     *
     * @var string $works
     */
    protected $works;

    /**
     * Holds a list of article's that relate to the event.
     *
     * @var array $articles
     */
    protected $articles;

    /**
     * Holds the event's price.
     *
     * @var string $price
     */
    protected $price;

    /**
     * Holds the event's tags.
     *
     * @var array $tags
     */
    protected $tags;

    /**
     * Holds the event's age restriction.
     *
     * @var string $ageRestriction
     */
    protected $ageRestriction;

    /**
     * Holds the event's involved people.
     *
     * @var string $involvedPeople
     */
    protected $involvedPeople;

    /**
     * Holds a bool reflecting if the event is closed/locked.
     *
     * @var bool $closed
     */
    protected $closed;

    /**
     * Tells whether the event owns a tip-dot.
     *
     * @var bool $hasTipPoint
     */
    protected $hasTipPoint;

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_NAME,
                self::PROP_TEXT,
                self::PROP_CONTENT_CREATED,
                self::PROP_CONTENT_UPDATED,
                self::PROP_EVENT_SCHEDULE,
                self::PROP_CATEGORY,
                self::PROP_SUBCATEGORY,
                self::PROP_CATEGORY_SRC,
                self::PROP_ASSETS,
                self::PROP_ARCHIVE,
                self::PROP_ORG_TITLE,
                self::PROP_SORT_TITLE,
                self::PROP_TICKETS,
                self::PROP_BOOK,
                self::PROP_DURATION,
                self::PROP_MEET_AT,
                self::PROP_HIGHLIGHT,
                self::PROP_KIDS_INFO,
                self::PROP_WORKS,
                self::PROP_INVOLVED_PEOPLE,
                self::PROP_AGE_RESTRICTION,
                self::PROP_PRICE,
                self::PROP_ARTICLES,
                self::PROP_TAGS,
                self::PROP_CLOSED,
                self::PROP_HAS_TIP_POINT
            )
        );
    }

    /**
     * Returns the event's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the event's text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the event's contentCreated.
     *
     * @return string
     */
    public function getContentCreated()
    {
        return $this->contentCreated;
    }

    /**
     * Returns the event's contentUpdated.
     *
     * @return string
     */
    public function getContentUpdated()
    {
        return $this->contentUpdated;
    }

    /**
     * Returns the event's category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Returns the event's subcategory.
     *
     * @return string
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * Returns the event's categorySrc.
     *
     * @return string
     */
    public function getCategorySrc()
    {
        return $this->categorySrc;
    }

    /**
     * Returns the event's eventSchedule.
     *
     * @return EventsSchedule
     */
    public function getEventSchedule()
    {
        return $this->eventSchedule;
    }

    /**
     * Returns the event's assets.
     *
     * @return array A list of ProjectAssetInfo identifiers.
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Returns the event's orgTitle.
     *
     * @return string
     */
    public function getOrgTitle()
    {
        return $this->orgTitle;
    }

    /**
     * Returns the event's sortTitle.
     *
     * @return string
     */
    public function getSortTitle()
    {
        return $this->sortTitle;
    }

    /**
     * Returns the event's tickets.
     *
     * @return string
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Returns the event's related book.
     *
     * @return string
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * Returns the event's duration.
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Returns the event's meetup location.
     *
     * @return string
     */
    public function getMeetAt()
    {
        return $this->meetAt;
    }

    /**
     * Returns the event's highlight.
     *
     * @return string
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * Returns the event's kids information.
     *
     * @return string
     */
    public function getKidsInfo()
    {
        return $this->kidsInfo;
    }

    /**
     * Returns the event's related works.
     *
     * @return string
     */
    public function getWorks()
    {
        return $this->works;
    }

    /**
     * Returns the event's closed/locked flag.
     *
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Returns the event's tags.
     *
     * @return array A list of strings.
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns the event's involved people.
     *
     * @return array
     */
    public function getInvolvedPeople()
    {
        return $this->involvedPeople;
    }

    /**
     * Returns the event's age restriction.
     *
     * @return string
     */
    public function getAgeRestriction()
    {
        return $this->ageRestriction;
    }

    /**
     * Returns the event's price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the event'sarchive entry.
     *
     * @return array
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Returns the event's has-tip-point flag.
     *
     * @return boolean
     */
    public function getHasTipPoint()
    {
        return $this->hasTipPoint;
    }

    /**
     * Returns the event's related articles.
     *
     * @return array
     */
    public function getArticles()
    {
        return $this->articles;
    }
}
