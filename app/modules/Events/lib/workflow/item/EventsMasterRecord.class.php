<?php

/**
 * The EventsMasterRecord reflects the toplevel datastructure of the events domain.
 * Basically it can be broken to the following relationships:
 * EventsMasterRecord -has-a-> EventSchedule -has-many-> EventLocation -has-many-> EventAppointment
 *
 * @version $Id: EventsMasterRecord.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsMasterRecord extends MasterRecord
{
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
     * @var string $contentCreated
     */
    protected $contentCreated;

    /**
     * Holds the date at which the event's content was last updated.
     *
     * @var string $contentUpdated
     */
    protected $contentUpdated;

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
     * Holds the event's category source.
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
     * @var array $articles A list of EventsArticle.
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
     * Factory method for creating new EventsMasterRecord instances.
     *
     * @var array $data
     *
     * @return EventsMasterRecord
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
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
     * @return array A list of EventsArticle.
     */
    public function getArticles()
    {
        return $this->articles;
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
     * Sets the event's archive entry.
     */
    protected function setArchive($archive)
    {
        if ($archive instanceof EventsArchiveEntry)
        {
            $this->archive = $archive;
        }
        else if(is_array($archive))
        {
            $this->archive = EventsArchiveEntry::fromArray($archive);
        }
    }

    /**
     * Sets the event's articles.
     *
     * @var array $articles Either a list containing EventsArticle instances or hydratable arrays, may be mixed together.
     */
    protected function setArticles(array $articles)
    {
        $this->articles = array();
        foreach ($articles as $article)
        {
            if ($article instanceof EventsArticle)
            {
                $this->articles[] = $article;
            }
            elseif (is_array($article))
            {
                $this->articles[] = EventsArticle::fromArray($article);
            }
        }
    }

    /**
     * Sets the event's schedule.
     *
     * @var mixed $eventSchedule Either a EventsSchedule instance or hydratable array.
     */
    protected function setEventSchedule($eventSchedule)
    {
        if ($eventSchedule instanceof EventsSchedule)
        {
            $this->eventSchedule = $eventSchedule;
        }
        elseif (is_array($eventSchedule))
        {
            $this->eventSchedule = EventsSchedule::fromArray($eventSchedule);
        }
    }
}
