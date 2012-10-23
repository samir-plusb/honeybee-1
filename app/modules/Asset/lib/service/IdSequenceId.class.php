<?php

class IdSequenceId extends BaseDocument
{
    protected $revision;

    protected $currentId;

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
    }

    public function getCurrentId()
    {
        return $this->currentId;
    }

    public function increment()
    {
        $this->currentId++;
        return $this->currentId;
    }
}

?>
