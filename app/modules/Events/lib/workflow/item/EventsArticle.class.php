<?php

/**
 * The EventsArticle class reflects the structure of one of an event's articles.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsArticle extends BaseDataObject
{
    /**
     * Holds the article's identifier.
     *
     * @var string $identifier
     */
    protected $identifier;

    /**
     * Holds the article's title.
     *
     * @var string $title
     */
    protected $title;

    /**
     * Holds the article's text.
     *
     * @var string $text
     */
    protected $text;

    /**
     * Holds the article's category.
     *
     * @var string $category
     */
    protected $category;

    /**
     * Holds the article's subcategory.
     *
     * @var string $subcategory
     */
    protected $subcategory;

    /**
     * Holds the article's category source.
     *
     * @var string $categorySrc
     */
    protected $categorySrc;

    /**
     * Holds the article's issue (year/number).
     *
     * @var string $issue
     */
    protected $issue;

    /**
     * Holds the article's whatever (int)...
     * Need to find out what this is (wicked)
     *
     * @var int $bx
     */
    protected $bx;

    /**
     * Holds the article's whatever (int)...
     * Need to find out what this is (wicked)
     *
     * @var int $by
     */
    protected $by;

    /**
     * Holds the article's whatever (string)...
     * Need to find out what this is (wicked)
     *
     * @var string $bu
     */
    protected $bu;

    /**
     * Holds the article's whatever (string)...
     * Need to find out what this is (wicked)
     *
     * @var string $bf
     */
    protected $bf;

    /**
     * Holds a reference to the article's picture.
     * Use the ProjectAssetService or asset api to get the real stuff.
     *
     * @string $pictureId
     */
    protected $pictureId;

    /**
     * Holds a reference to the article's priority.
     *
     * @string $priority
     */
    protected $priority;

    /**
     * Holds a reference to the article's nodeValue (usually not set).
     *
     * @string $nodeValue
     */
    protected $nodeValue;

    /**
     * Holds a list of people related to the article.
     *
     * @var array $relatedPeople
     */
    protected $relatedPeople;

    /**
     * Holds a list of identifiers representing related events.
     *
     * @var array $eventIds List of event identifiers.
     */
    protected $eventIds;

    /**
     * Holds a list of identifiers representing related locations.
     *
     * @var array $locationIds List of event identifiers.
     */
    protected $locationIds;

    /**
     * Holds a list of identifiers representing related archive entries.
     *
     * @var array $archiveIds List of archive identifiers.
     */
    protected $archiveIds;

    /**
     * Factory method for creating new EventsArticle instances.
     *
     * @var array $data
     *
     * @return EventsArticle
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the article's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the article's text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the article's category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Returns the article's subcategory.
     *
     * @return string
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * Returns the article's categorySrc.
     *
     * @return string
     */
    public function getCategorySrc()
    {
        return $this->categorySrc;
    }

    /**
     * Returns the article's issue.
     *
     * @return string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Returns the article's 'bx' property.
     *
     * @return int
     */
    public function getBx()
    {
        return $this->bx;
    }

    /**
     * Returns the article's 'by' property.
     *
     * @return int
     */
    public function getBy()
    {
        return $this->by;
    }

    /**
     * Returns the article's 'bu' property.
     *
     * @return string
     */
    public function getBu()
    {
        return $this->bu;
    }

    /**
     * Returns the article's 'bf' property.
     *
     * @return string
     */
    public function getBf()
    {
        return $this->bf;
    }

    /**
     * Returns the article's picture identifier.
     *
     * @return string
     */
    public function getPictureId()
    {
        return $this->pictureId;
    }

    /**
     * Returns the article's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the article's node-value (usually not set).
     *
     * @return string
     */
    public function getNodeValue()
    {
        return $this->nodeValue;
    }

    /**
     * Returns the article's priority.
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the article's related event ids.
     *
     * @return array List of event identifiers.
     */
    public function getEventIds()
    {
        return $this->eventIds;
    }

    /**
     * Returns the article's related location ids.
     *
     * @return array List of location identifiers.
     */
    public function getLocationIds()
    {
        return $this->locationIds;
    }

    /**
     * Returns the article's related archive ids.
     *
     * @return array List of archive identifiers.
     */
    public function getArchiveIds()
    {
        return $this->archiveIds;
    }

    /**
     * Returns the article's related people.
     *
     * @return array
     */
    public function getRelatedPeople()
    {
        return $this->relatedPeople;
    }
}
