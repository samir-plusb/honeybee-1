<?php

class FrontendMovieDocument extends BaseDocument
{
    const TYPE_PREFIX = 'movie-';

    protected $revision;

    protected $title;

    protected $teaser;

    protected $director = array();

    protected $actors = array();

    protected $isRecommendation = FALSE;

    protected $rental;

    protected $genre;

    protected $fsk;

    protected $country;

    protected $releaseDate;

    protected $duration;

    protected $year;

    protected $screenings = array();

    protected $theaters = array();

    protected $media = array();

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
        $this->onPropertyChanged("revision");
        return $this;
    }

    public function setIdentifier($identifier)
    {
        if (0 !== strpos($identifier, self::TYPE_PREFIX))
        {
            $identifier = self::TYPE_PREFIX . $identifier;
        }
        $this->identifier = $identifier;
    }

    public function getScreenings()
    {
        return $this->screenings;
    }

    public function getTheaters()
    {
        return $this->theaters;
    }
}

?>
