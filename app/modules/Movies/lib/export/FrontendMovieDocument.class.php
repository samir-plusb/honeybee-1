<?php

class FrontendMovieDocument extends BaseDocument
{
    protected $revision;

    protected $title;

    protected $teaser;

    protected $director = array();

    protected $actors = array();

    protected $topMovie = FALSE;

    protected $rental;

    protected $genre;

    protected $fsk;

    protected $country;

    protected $releaseDate;

    protected $duration;

    protected $year;

    protected $screenings = array();

    protected $reviews = array();

    protected $media = array();

    /**
     * Holds a unique string representation of this object that can be used in urls.
     * 
     * @var string
     */
    protected $slug;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setTopMovie($topMovie)
    {
        $this->topMovie = (bool)$topMovie;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
        $this->onPropertyChanged("revision");
        return $this;
    }

    public function getScreenings()
    {
        return $this->screenings;
    }

    public function getMedia()
    {
        return $this->media;
    }
}

