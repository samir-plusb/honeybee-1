<?php

/**
 * The TipFrontendEvent class is represents the structure of EventsWorkflowItems
 * from the view of the Tip-frontend.
 *
 * @version         $Id: TipFrontendEventLocation.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendEvent extends BaseDocument
{
    /**
     * Holds the event document's revision.
     *
     * @var string $name
     */
    protected $revision;

    /**
     * Holds the event's title.
     *
     * @var string $name
     */
    protected $title;

    /**
     * Holds the event title in a different version used for sorting.
     *
     * @var string $sortTitle
     */
    protected $sortTitle;

    /**
     * Holds a text describing the event.
     * Is either te originally imported text or
     * the text of the article with the lowest priority.
     *
     * @var string $text
     */
    protected $text;

    /**
     * An array holding the meta data and id associated with 
     * an event's image.
     *
     * @param array $image
     *
     * 'data': Holds the image's base64 encoded data.
     * 'width': Holds the assets's (image's) width (surprise!).
     * 'height': Holds the assets's (image's) height.
     * 'mime': Holds the asset's mime type.
     * 'filename': Holds the images original filename.
     * 'modified': Holds the image's last modified date (DATE_ISO8601 format).
     * 'copyright': Holds the images copyright info.
     * 'copyright_url': Holds the images copyright url.
     * 'caption': Holds the images caption text.
     */
    protected $image;

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
     * Holds the event's schedule data.
     *
     * @var array $eventSchedule
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
     * Holds the event's (filepool assets data)
     *
     * @var array $filepool An array with the keys 'images' and 'videos'
     */
    protected $filepool;

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
     * Holds a list of TipFrontendEventLocation instances,
     * that can be used to look up schedule->location->locationId's.
     *
     * @var array
     */
    protected $locations;

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
     * Returns the event document's revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Returns the event document's revision.
     *
     * @return string
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
        $this->onPropertyChanged("revision");
        return $this;
    }

    /**
     * Returns the event's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * Returns the event's text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Return the event's image.
     *
     * @return array
     */
    public function getImage()
    {
        return $this->image;
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
     * @return array A list of ProjectAssetInfo identifiers together with their meta data.
     */
    public function getFilepool()
    {
        return $this->filepool;
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
     * Returns the event's archive data.
     *
     * @return array
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Returns a list of TipFrontendEventLocation
     *
     * @return array
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Sets the frontend event's locations.
     *
     * @var array List of TipFrontendLocation or equivalent array representations
     */
    protected function setLocations(array $locations)
    {
        $this->locations = array();
        foreach ($locations as $location)
        {
            if ($location instanceof TipFrontendLocation)
            {
                $this->locations[$location->getIdentifier()] = $location;
            }
            elseif (is_array($location) && ! empty($location))
            {
                $newLocation = TipFrontendLocation::fromArray($location);
                $this->locations[$newLocation->getIdentifier()] = $newLocation; 
            }
        }
    }
}
