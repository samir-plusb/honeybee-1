<?php

namespace Honeybee\Core\Storage\CouchDb;

class Conflict extends \Exception
{
    protected $given_revision;

    protected $head_revision;

    public function getGivenRevision()
    {
        return $this->given_revision;
    }

    public function setGivenRevision($revision)
    {
        $this->given_revision = $revision;
    }

    public function getHeadRevision()
    {
        return $this->head_revision;
    }

    public function setHeadRevision($revision)
    {
        $this->head_revision = $revision;
    }
}
