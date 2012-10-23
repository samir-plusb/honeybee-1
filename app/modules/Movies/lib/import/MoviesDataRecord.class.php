<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import
 */
abstract class MoviesDataRecord extends BaseDataRecord
{
    const PROP_TITLE = 'title';

    const PROP_TEASER = 'teaser';

    const PROP_SUBLINE = 'subline';

    const PROP_WEBSITE = 'website';

    const PROP_DIRECTOR = 'director';

    const PROP_ACTORS = 'actors';

    const PROP_RENTAL = 'rental';

    const PROP_GENRE = 'genre';

    const PROP_FSK = 'fsk';

    const PROP_COUNTRY = 'country';

    const PROP_RELEASE_DATE = 'releaseDate';

    const PROP_YEAR = 'year';

    const PROP_DURATION = 'duration';

    const PROP_SCREENINGS = 'screenings';

    const PROP_MEDIA = 'media';

    const PROP_REVIEWS = 'reviews';

    const PROP_IMPORT_IDENTIFIER = 'importIdentifier';

    protected $title;

    protected $teaser;

    protected $subline;

    protected $website;

    protected $director = array();

    protected $actors = array();

    protected $rental;

    protected $genre;

    protected $fsk;

    protected $country;

    protected $releaseDate;

    protected $duration;

    protected $year;

    protected $screenings = array();

    protected $media = array();

    protected $reviews = array();

    protected $importIdentifier;

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
                self::PROP_TITLE,
                self::PROP_TEASER,
                self::PROP_SUBLINE,
                self::PROP_WEBSITE,
                self::PROP_DIRECTOR,
                self::PROP_ACTORS,
                self::PROP_RENTAL,
                self::PROP_GENRE,
                self::PROP_FSK,
                self::PROP_COUNTRY,
                self::PROP_RELEASE_DATE,
                self::PROP_YEAR,
                self::PROP_DURATION,
                self::PROP_SCREENINGS,
                self::PROP_MEDIA,
                self::PROP_REVIEWS,
                self::PROP_IMPORT_IDENTIFIER
            )
        );
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getTeaser()
    {
        return $this->teaser;
    }

    public function getSubline()
    {
        return $this->subline;
    }

    public function getDirector()
    {
        return $this->director;
    }

    public function getActors()
    {
        return $this->actors;
    }

    public function getRental()
    {
        return $this->rental;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function getFsk()
    {
        return $this->fsk;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getScreenings()
    {
        return $this->screenings;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getReviews()
    {
        return $this->reviews;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function getImportIdentifier()
    {
         return $this->importIdentifier;
    }

    protected function buildImportIdentifier($identifier)
    {
         return 'movies-telavision:' . $identifier;
    }
}
