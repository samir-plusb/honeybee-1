<?php

class FrontendTheaterDocument extends BaseDocument
{
    protected $revision;

    protected $coreData;

    protected $salesData;

    protected $detailData;

    protected $additionalInfo;

    protected $lastModified;

    protected $screenings = array();

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
}
