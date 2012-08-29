<?php

/**
 * The EventsArchiveEntry class reflects the structure of an event's archive entry.
 *
 * @version $Id: EventsArchiveEntry.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsArchiveEntry extends BaseDataObject
{
    /**
     * Holds the article's identifier.
     *
     * @var string $identifier
     */
    protected $identifier;

    /**
     * Holds the archive entries content creation date.
     *
     * @var string $contentCreated
     */
    protected $contentCreated;

   /**
     * Holds the archive entries content update date.
     *
     * @var string $contentUpdated
     */
    protected $contentUpdated;

    /**
     * Holds the archive entry's title.
     *
     * @var string $title
     */
    protected $title;

    /**
     * Holds the archive entry's sort-title (used for sorting, surprise^^).
     *
     * @var string $sortTitle
     */
    protected $sortTitle;

    /**
     * Holds the archive entry's description.
     *
     * @var string $description
     */
    protected $description;

    /**
     * Holds the archive entry's age rating (german fsk).
     *
     * @var string $ageRating
     */
    protected $ageRating;

    /**
     * Holds the archive entry's duration.
     *
     * @var string $duration
     */
    protected $duration;

    /**
     * Holds the archive entry's year.
     *
     * @var string $year
     */
    protected $year;

    /**
     * Holds the archive entry's country.
     *
     * @var string $country
     */
    protected $country;

    /**
     * Holds the archive entry's tags.
     *
     * @var array $tags
     */
    protected $tags;

    /**
     * Holds a list of related people.
     *
     * @var array $relatedPeople
     */
    protected $relatedPeople;

    /**
     * Holds a list of filepool media assets.
     *
     * @var array $filepool
     */
    protected $filePool;

    /**
     * Holds a bool flag indicating whether we are marked as a tip-point.
     *
     * @var boolean $hasTipPoint
     */
    protected $hasTipPoint;

    /**
     * Holds a bool flag indicating whether we are marked as a kids-movie.
     *
     * @var boolean $isKidsMovie
     */
    protected $isKidsMovie;

    /**
     * Holds a our current rating value.
     * @todo Find out the min and max possible values and consider them.
     *
     * @var int $rating
     */
    protected $rating;

    /**
     * Holds the archive entry's movie number.
     *
     * @var int $movieNumber
     */
    protected $movieNumber;

    /**
     * Holds the archive entry's movie start date.
     *
     * @var string $movieStart
     */
    protected $movieStart;

    /**
     * Holds the archive entry's original artwork/master copy.
     *
     * @var string $originalArtwork
     */
    protected $originalArtwork;

    /**
     * Holds the archive entry's movie-series.
     *
     * @var string $movieSeries
     */
    protected $movieSeries;

    /**
     * Factory method for creating new EventsArchiveEntry instances.
     *
     * @var array $data
     *
     * @return EventsArchiveEntry
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the archive entiry's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns archive entries content creation date.
     *
     * @return string
     */
    public function getContentCreated()
    {
        return $this->contentCreated;
    }
    
   /**
     * Returns archive entries content updated date.
     *
     * @return string
     */
    public function getContentUpdated()
    {
        return $this->contentUpdated;
    }

    /**
     * Returns the archive entiry's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the archive entiry's description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the archive entiry's sort-title.
     *
     * @return string
     */
    public function getSortTitle()
    {
        return $this->sortTitle;
    }

    /**
     * Returns the archive entiry's age-rating.
     *
     * @return string
     */
    public function getAgeRating()
    {
        return $this->ageRating;
    }

    /**
     * Returns the archive entiry's duration.
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Returns the archive entiry's year.
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Returns the archive entiry's country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns the archive entiry's tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns the archive entiry's related people.
     *
     * @return array
     */
    public function getRelatedPeople()
    {
        return $this->relatedPeople;
    }

    /**
     * Returns the archive entiry's file pool media.
     *
     * @return array
     */
    public function getFilePool()
    {
        return $this->filePool;
    }

    /**
     * Returns the archive entiry's hasTipPoint flag.
     *
     * @return boolean
     */
    public function getHasTipPoint()
    {
        return $this->hasTipPoint;
    }

    /**
     * Returns the archive entiry's isKidsMovie flag.
     *
     * @return boolean
     */
    public function getIsKidsMovie()
    {
        return $this->isKidsMovie;
    }

    /**
     * Returns the archive entiry's rating.
     * @todo Find out the min and max possible values and consider them.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Returns the archive entiry's movie number.
     *
     * @return int
     */
    public function getMovieNumber()
    {
        return $this->movieNumber;
    }

    /**
     * Returns the archive entiry's movie start date.
     *
     * @return string
     */
    public function getMovieStart()
    {
        return $this->movieStart;
    }

    /**
     * Returns the archive entiry's original artwork/master copy.
     *
     * @return string
     */
    public function getOriginalArtwork()
    {
        return $this->originalArtwork;
    }

    /**
     * Returns the archive entiry's movie-series.
     *
     * @return string
     */
    public function getMovieSeries()
    {
        return $this->movieSeries;
    }
}
            