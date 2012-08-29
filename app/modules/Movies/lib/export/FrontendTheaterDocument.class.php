<?php

class FrontendTheaterDocument extends BaseDocument
{
    const TYPE_PREFIX = 'theater-';

    protected $revision;

    protected $coreData;

    protected $salesData;

    protected $detailData;

    protected $attributes;

    protected $lastModified;

    protected $screenings = array();

    protected $movies = array();

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

    public function getMovies()
    {
        return $this->movies;
    }
}

?>
