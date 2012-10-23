<?php

/**
 * The MoviesMasterRecord holds the main data of a Movies item.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Movies
 * @subpackage Workflow/Item
 */
class MoviesMasterRecord extends MasterRecord
{
    protected $title;

    protected $teaser;

    protected $subline;

    protected $website;

    protected $reviews;

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

    protected $isRecommendation = FALSE;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTeaser()
    {
        return $this->teaser;
    }

    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    public function getDirector()
    {
        return $this->director;
    }

    public function setDirector(array $director)
    {
        $this->director = array_values(
            array_filter($director, function($item)
            {
                return ! empty($item);
            })
        );
    }

    public function getActors()
    {
        return $this->actors;
    }

    public function setActors(array $actors)
    {
        $this->actors = array_values(
            array_filter($actors, function($actor)
            {
                return ! empty($actor);
            })
        );
    }

    public function getRental()
    {
        return $this->rental;
    }

    public function setRental($rental)
    {
        $this->rental = $rental;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    public function getFsk()
    {
        return $this->fsk;
    }

    public function setFsk($fsk)
    {
        $this->fsk = $fsk;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = empty($releaseDate) ? NULL : $releaseDate;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getScreenings()
    {
        return $this->screenings;
    }

    public function setScreenings(array $screenings)
    {
        $this->screenings = $screenings;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia(array $media)
    {
        $this->media = $media;
    }

    public function getIsRecommendation()
    {
        return $this->isRecommendation;
    }

    public function setIsRecommendation($isRecommendation)
    {
        $this->isRecommendation = $isRecommendation;
    }

    public function getSubline()
    {
        return $this->subline;
    }

    public function setSubline($subline)
    {
        $this->subline = $subline;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }
}
