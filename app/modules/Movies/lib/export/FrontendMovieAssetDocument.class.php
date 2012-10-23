<?php

class FrontendMovieAssetDocument extends BaseDocument
{
    protected $revision;

    protected $data;

    protected $width;

    protected $height;

    protected $mime;

    protected $filename;

    protected $modified;

    protected $copyright;

    protected $copyrightUrl;

    protected $caption;

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
}
